<?php

declare(strict_types=1);

namespace UBOS\Puck\Domain\Repository;

use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Abstract base repository for Record-based domain objects.
 *
 * Provides a clean API similar to Extbase repositories but optimized for the Record system.
 * Uses TYPO3's Restriction system and respects TCA configuration automatically.
 */
#[Autoconfigure(public: true)]
abstract class AbstractRecordRepository implements Log\LoggerAwareInterface
{
	use Log\LoggerAwareTrait;

	protected ?Core\Database\ConnectionPool $connectionPool = null;
	protected ?Core\Domain\RecordFactory $recordFactory = null;
	protected ?Context $context = null;

	// Runtime cache (per request) - similar to Extbase's Identity Map
	// Stores Record objects by their actual UID
	protected array $runtimeCache = [];
	protected bool $enableRuntimeCache = true;

	// Query settings (similar to Extbase QuerySettings)
	protected bool $respectStoragePage = false;
	protected int $storagePid = 0;
	protected bool $respectSysLanguage = true;
	protected ?int $languageUid = null; // null = use from context
	protected bool $ignoreEnableFields = false;
	protected bool $includeDeleted = false;
	protected bool $returnRawQueryResult = false;

	public function injectConnectionPool(Core\Database\ConnectionPool $connectionPool): void
	{
		$this->connectionPool = $connectionPool;
	}

	public function injectRecordFactory(Core\Domain\RecordFactory $recordFactory): void
	{
		$this->recordFactory = $recordFactory;
	}

	public function injectContext(Context $context): void
	{
		$this->context = $context;
	}

	abstract public function getTableName(): string;

	// ========== Configuration Methods (Extbase-style) ==========

	public function setRespectStoragePage(bool $respectStoragePage): self
	{
		$this->respectStoragePage = $respectStoragePage;
		return $this;
	}

	public function setStoragePid(int $storagePid): self
	{
		$this->storagePid = $storagePid;
		$this->respectStoragePage = true; // Automatically enable when setting a storage page
		return $this;
	}

	public function setRespectSysLanguage(bool $respectSysLanguage): self
	{
		$this->respectSysLanguage = $respectSysLanguage;
		return $this;
	}

	public function setLanguageUid(?int $languageUid): self
	{
		$this->languageUid = $languageUid;
		$this->respectSysLanguage = true;
		return $this;
	}

	/**
	 * Get the current language UID.
	 * Falls back to context if not explicitly set.
	 */
	protected function getLanguageUid(): int
	{
		if ($this->languageUid !== null) {
			return $this->languageUid;
		}

		// Get from context (current page language in frontend)
		if ($this->context) {
			try {
				return $this->context->getPropertyFromAspect('language', 'id', 0);
			} catch (\Exception $e) {
				// Fallback if language aspect not available
				return 0;
			}
		}

		return 0;
	}

	public function setIgnoreEnableFields(bool $ignoreEnableFields): self
	{
		$this->ignoreEnableFields = $ignoreEnableFields;
		return $this;
	}

	public function setIncludeDeleted(bool $includeDeleted): self
	{
		$this->includeDeleted = $includeDeleted;
		return $this;
	}

	public function setReturnRawQueryResult(bool $returnRawQueryResult): self
	{
		$this->returnRawQueryResult = $returnRawQueryResult;
		return $this;
	}

	public function setEnableRuntimeCache(bool $enableRuntimeCache): self
	{
		$this->enableRuntimeCache = $enableRuntimeCache;
		if (!$enableRuntimeCache) {
			$this->clearRuntimeCache();
		}
		return $this;
	}

	public function clearRuntimeCache(): void
	{
		$this->runtimeCache = [];
	}

	// ========== CRUD Methods ==========

	public function create(array $data): int
	{
		$builder = $this->getQueryBuilder();
		$builder
			->insert($this->getTableName())
			->values($data)
			->executeStatement();

		return (int)$builder->getConnection()->lastInsertId();
	}

	public function findAll(?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
	{
		$builder = $this->createQuery();

		if ($orderBy) {
			foreach ($orderBy as $field => $direction) {
				$builder->addOrderBy($field, $direction);
			}
		}

		if ($limit !== null) {
			$builder->setMaxResults($limit);
		}

		if ($offset !== null) {
			$builder->setFirstResult($offset);
		}

		return $this->execute($builder);
	}

	public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
	{
		$builder = $this->createQuery();

		foreach ($criteria as $field => $value) {
			$builder->andWhere(
				$builder->expr()->eq($field, $builder->createNamedParameter($value))
			);
		}

		if ($orderBy) {
			foreach ($orderBy as $field => $direction) {
				$builder->addOrderBy($field, $direction);
			}
		}

		if ($limit !== null) {
			$builder->setMaxResults($limit);
		}

		if ($offset !== null) {
			$builder->setFirstResult($offset);
		}

		return $this->execute($builder);
	}

	public function findOneBy(array $criteria): Core\Domain\Record|array|null
	{
		$results = $this->findBy($criteria, limit: 1);
		return $results[0] ?? null;
	}

	public function findByUid(int $uid): Core\Domain\Record|array|null
	{
		if ($this->shouldUseCache()) {
			$cacheKey = $this->getCacheKeyForUid($uid);
			if (isset($this->runtimeCache[$cacheKey])) {
				return $this->runtimeCache[$cacheKey];
			}
		}

		$builder = $this->createQuery();

		// Language-aware UID lookup if applicable
		if ($this->respectSysLanguage && $this->hasLanguageSupport()) {
			$l10nParentField = $this->getTcaValue('transOrigPointerField');
			if ($l10nParentField) {
				$builder->andWhere(
					$builder->expr()->or(
						$builder->expr()->eq('uid', $builder->createNamedParameter($uid)),
						$builder->expr()->eq($l10nParentField, $builder->createNamedParameter($uid)),
					)
				);
				$results = $this->execute($builder);
				$result = $results[0] ?? null;

				// Cache result if appropriate
				if ($this->shouldUseCache() && $result !== null) {
					$this->runtimeCache[$this->getCacheKeyForUid($uid)] = $result;
				}

				return $result;
			}
		}

		// Standard UID lookup
		$builder->andWhere($builder->expr()->eq('uid', $builder->createNamedParameter($uid)));
		$results = $this->execute($builder);
		$result = $results[0] ?? null;

		// Cache result if appropriate
		if ($this->shouldUseCache() && $result !== null) {
			$this->runtimeCache[$this->getCacheKeyForUid($uid)] = $result;
		}

		return $result;
	}

	public function countAll(): int
	{
		$builder = $this->createQuery();
		return (int)$builder
			->count('uid')
			->executeQuery()
			->fetchOne();
	}

	public function countBy(array $criteria): int
	{
		$builder = $this->createQuery();

		foreach ($criteria as $field => $value) {
			$builder->andWhere(
				$builder->expr()->eq($field, $builder->createNamedParameter($value))
			);
		}

		return (int)$builder
			->count('uid')
			->executeQuery()
			->fetchOne();
	}

	public function update(int $uid, array $data): void
	{
		$builder = $this->getQueryBuilder();
		$builder
			->update($this->getTableName())
			->where($builder->expr()->eq('uid', $builder->createNamedParameter($uid)));

		foreach ($data as $column => $value) {
			$builder->set($column, $value);
		}

		$builder->executeStatement();

		// Clear cache since data changed
		$this->clearRuntimeCache();
	}

	public function updateBy(array $criteria, array $data): void
	{
		$builder = $this->getQueryBuilder();
		$builder->update($this->getTableName());

		foreach ($criteria as $field => $value) {
			$builder->andWhere(
				$builder->expr()->eq($field, $builder->createNamedParameter($value))
			);
		}

		foreach ($data as $column => $value) {
			$builder->set($column, $value);
		}

		$builder->executeStatement();

		// Clear cache since data changed
		$this->clearRuntimeCache();
	}

	public function remove(int $uid, bool $softDelete = true): void
	{
		if ($softDelete && $this->hasDeletedField()) {
			$deletedField = $this->getTcaValue('delete');
			if ($deletedField) {
				$this->update($uid, [$deletedField => 1]);
				return;
			}
		}

		// Hard delete if soft delete not possible or not requested
		$this->hardDelete($uid);

		// Cache already cleared by update() or hardDelete()
	}

	public function removeBy(array $criteria, bool $softDelete = true): void
	{
		if ($softDelete && $this->hasDeletedField()) {
			$deletedField = $this->getTcaValue('delete');
			if ($deletedField) {
				$this->updateBy($criteria, [$deletedField => 1]);
				return;
			}
		}

		// Hard delete if soft delete not possible or not requested
		$builder = $this->getQueryBuilder();
		$builder->delete($this->getTableName());

		foreach ($criteria as $field => $value) {
			$builder->andWhere(
				$builder->expr()->eq($field, $builder->createNamedParameter($value))
			);
		}

		$builder->executeStatement();

		// Clear cache since data changed
		$this->clearRuntimeCache();
	}

	protected function hardDelete(int $uid): void
	{
		$builder = $this->getQueryBuilder();
		$builder
			->delete($this->getTableName())
			->where($builder->expr()->eq('uid', $builder->createNamedParameter($uid)))
			->executeStatement();

		// Clear cache since data changed
		$this->clearRuntimeCache();
	}

	// ========== Query Building ==========

	/**
	 * Creates a query builder with restrictions applied based on repository settings.
	 */
	protected function createQuery(): QueryBuilder
	{
		$builder = $this->getQueryBuilder();
		$builder->select('*')->from($this->getTableName());

		// Apply restrictions
		$this->applyRestrictions($builder);

		// Apply storage page restriction
		if ($this->respectStoragePage) {
			$builder->andWhere(
				$builder->expr()->eq('pid', $builder->createNamedParameter($this->storagePid))
			);
		}

		// Apply language restriction
		if ($this->respectSysLanguage && $this->hasLanguageSupport()) {
			$languageField = $this->getTcaValue('languageField');
			$builder->andWhere(
				$builder->expr()->eq($languageField, $builder->createNamedParameter($this->getLanguageUid()))
			);
		}

		return $builder;
	}

	/**
	 * Apply TYPO3 restriction system based on repository settings.
	 */
	protected function applyRestrictions(QueryBuilder $builder): void
	{
		$restrictions = $builder->getRestrictions()->removeAll();

		// Always apply workspace restriction
		if ($this->context) {
			$workspaceId = $this->context->getPropertyFromAspect('workspace', 'id', 0);
			if ($workspaceId > 0) {
				$restrictions->add(new WorkspaceRestriction($workspaceId));
			}
		}

		// Apply enable fields restrictions unless explicitly disabled
		if (!$this->ignoreEnableFields) {
			// Deleted
			if (!$this->includeDeleted && $this->hasDeletedField()) {
				$restrictions->add(new DeletedRestriction());
			}

			// Hidden (respect backend context)
			if ($this->hasHiddenField()) {
				$includeHidden = $this->context
					? $this->context->getPropertyFromAspect('visibility', 'includeHiddenContent', false)
					: false;

				if (!$includeHidden) {
					$restrictions->add(new HiddenRestriction());
				}
			}

			// Starttime
			if ($this->hasStarttimeField()) {
				$restrictions->add(new StartTimeRestriction());
			}

			// Endtime
			if ($this->hasEndtimeField()) {
				$restrictions->add(new EndTimeRestriction());
			}
		}
	}

	/**
	 * Execute query and return results (either as Records or raw arrays).
	 */
	protected function execute(QueryBuilder $builder): array
	{
		$rows = $builder->executeQuery()->fetchAllAssociative();

		if ($this->returnRawQueryResult) {
			return $rows;
		}

		return $this->toRecords($rows);
	}

	protected function toRecords(array $rows): array
	{
		if (empty($rows)) {
			return [];
		}

		$records = [];
		foreach ($rows as $row) {
			if ($row) {
				$records[] = $this->recordFactory->createResolvedRecordFromDatabaseRow(
					$this->getTableName(),
					$row
				);
			}
		}

		return $records;
	}

	protected function getQueryBuilder(): QueryBuilder
	{
		return $this->connectionPool->getQueryBuilderForTable($this->getTableName());
	}

	// ========== Runtime Cache Helpers ==========

	/**
	 * Generate cache key for a UID lookup.
	 * Includes all settings that affect query results.
	 */
	protected function getCacheKeyForUid(int $uid): string
	{
		return sprintf(
			'%s|%d|%d|%d|%d|%d|%d|%d',
			$this->getTableName(),
			$uid,
			$this->getLanguageUid(),
			$this->storagePid,
			(int)$this->respectStoragePage,
			(int)$this->ignoreEnableFields,
			(int)$this->includeDeleted,
			(int)$this->returnRawQueryResult
		);
	}

	/**
	 * Check if runtime cache should be used.
	 * Cache is always enabled unless explicitly disabled.
	 */
	protected function shouldUseCache(): bool
	{
		return $this->enableRuntimeCache;
	}

	// ========== TCA Helpers ==========

	protected function getTca(): ?array
	{
		return $GLOBALS['TCA'][$this->getTableName()] ?? null;
	}

	protected function getTcaValue(string $path): mixed
	{
		$tca = $this->getTca();
		if (!$tca) {
			return null;
		}

		// Support paths like 'ctrl.delete' or just 'delete'
		if (str_contains($path, '.')) {
			$parts = explode('.', $path);
			$value = $tca;
			foreach ($parts as $part) {
				if (!isset($value[$part])) {
					return null;
				}
				$value = $value[$part];
			}
			return $value;
		}

		return $tca['ctrl'][$path] ?? null;
	}

	protected function hasLanguageSupport(): bool
	{
		return !empty($this->getTcaValue('languageField'));
	}

	protected function hasDeletedField(): bool
	{
		return !empty($this->getTcaValue('delete'));
	}

	protected function hasHiddenField(): bool
	{
		return !empty($this->getTcaValue('enablecolumns')['disabled'] ?? null);
	}

	protected function hasStarttimeField(): bool
	{
		return !empty($this->getTcaValue('enablecolumns')['starttime'] ?? null);
	}

	protected function hasEndtimeField(): bool
	{
		return !empty($this->getTcaValue('enablecolumns')['endtime'] ?? null);
	}
}
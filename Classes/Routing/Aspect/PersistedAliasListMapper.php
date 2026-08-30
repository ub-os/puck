<?php

namespace UBOS\Puck\Routing\Aspect;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

/**
 * Routing aspect mapper that maps a (default: comma-separated) list of UIDs to a (default: dot-separated) list of slugs.
 *
 * This is an extension of the PersistedAliasMapper that allows for lists of UIDs instead of a single UID.
 */
class PersistedAliasListMapper extends PersistedAliasMapper
{

	public function __construct(array $settings)
	{
		parent::__construct($settings);
		$this->uidSeparator = $settings['uidSeparator'] ?? ',';
		$this->slugSeparator = $settings['slugSeparator'] ?? '.';
	}

	public function generate(string $value): ?string
	{
		$results = [];
		foreach (explode($this->uidSeparator, $value) as $val) {
			$result = $this->resolveOverlay($this->findByIdentifier($val));
			if (isset($result[$this->routeFieldName])) {
				$results[] = $result[$this->routeFieldName];
			}
		}
		if (!$results) {
			return null;
		}
		return $this->purgeRouteValuePrefix(
			implode($this->slugSeparator, $results)
		);
	}

	public function resolve(string $value): ?string
	{
		$value = $this->routeValuePrefix . $this->purgeRouteValuePrefix($value);
		$results = [];
		foreach (explode($this->slugSeparator, $value) as $val) {
			$res = $this->findByRouteFieldValue($val);
			if (!is_array($res)) {
				// one slug in the list did not resolve -> the whole list is invalid
				return null;
			}
			if ((int)($res[$this->languageParentFieldName] ?? 0) > 0) {
				$results[] = $res[$this->languageParentFieldName];
			} elseif (isset($res['uid'])) {
				$results[] = $res['uid'];
			} else {
				return null;
			}
		}
		if ($results === []) {
			return null;
		}
		return implode($this->uidSeparator, $results);
	}

	protected function findByIdentifier(string $value): ?array
	{
		$languageAware = $this->languageFieldName !== null && $this->languageParentFieldName !== null;
		$languageIds = $this->resolveAllRelevantLanguageIds();
		if (!MathUtility::canBeInterpretedAsInteger($value)) {
			return null;
		}
		$queryBuilder = $this->createQueryBuilder();
		if ($languageAware && $languageIds && $languageIds !== [-1, 0]) {
			$where = [
				$queryBuilder->expr()->or(
					$queryBuilder->expr()->eq(
						'uid',
						$queryBuilder->createNamedParameter($value, Connection::PARAM_INT)),
					$queryBuilder->expr()->eq(
						$this->languageParentFieldName,
						$queryBuilder->createNamedParameter($value, Connection::PARAM_INT)),
				),
				$queryBuilder->expr()->in(
					$this->languageFieldName,
					$queryBuilder->createNamedParameter($this->resolveAllRelevantLanguageIds(), Connection::PARAM_INT_ARRAY)
				),
			];
		} else {
			$where = [
				$queryBuilder->expr()->eq(
					'uid',
					$queryBuilder->createNamedParameter($value, Connection::PARAM_INT)
				)
			];
		}
		$result = $queryBuilder
			->select(...$this->persistenceFieldNames)
			->where(...$where)
			->executeQuery()
			->fetchAssociative();
		return $result !== false ? $result : null;
	}
}
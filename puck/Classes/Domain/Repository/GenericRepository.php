<?php

declare(strict_types=1);

namespace UBOS\Puck\Domain\Repository;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Generic repository for tables without a dedicated repository class.
 *
 * Provides a fluent interface for configuring and querying any table.
 * All table structure information is read from TCA automatically.
 *
 * Example usage:
 * ```php
 * $items = $this->genericRepository
 *     ->forTable('tx_myext_domain_model_item')
 *     ->setLanguageUid(0)
 *     ->setStoragePid(123)
 *     ->findAll();
 * ```
 */
#[Autoconfigure(public: true, shared: false)]
class GenericRepository extends AbstractRecordRepository
{
	protected string $tableName;

	public function getTableName(): string
	{
		return $this->tableName;
	}

	/**
	 * Configure the repository for a specific table.
	 * All column information is read from TCA automatically.
	 *
	 * Note: Changing tables clears the runtime cache.
	 *
	 * @param string $tableName The database table name
	 * @return self Returns itself for fluent interface
	 */
	public function forTable(string $tableName): self
	{
		// Clear cache if table changed (cached records from old table are invalid)
		if (isset($this->tableName) && $this->tableName !== $tableName) {
			$this->clearRuntimeCache();
		}

		$this->tableName = $tableName;
		return $this;
	}
}
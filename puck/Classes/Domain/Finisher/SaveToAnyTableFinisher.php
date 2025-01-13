<?php

namespace UBOS\Puck\Domain\Finisher;

use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveToAnyTableFinisher extends AbstractFinisher
{
	protected function executeInternal(): void
	{
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable($this->settings['table']);
		$values = [
			'pid' => (int)($this->settings['storagePage'] ?: $this->formRecord->getPid()),
		];
		foreach ($this->settings['mapping'] as $column => $field) {
			$values[$column] = $this->formValues[$field] ?? '';
		}
		$queryBuilder->insert($this->settings['table'])
			->values($values)
			->executeQuery();
	}
}
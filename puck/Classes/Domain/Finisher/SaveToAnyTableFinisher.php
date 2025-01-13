<?php

namespace UBOS\Puck\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveToAnyTableFinisher extends AbstractFinisher
{
	protected function executeInternal(): void
	{
		$this->settings = array_merge([
			'table' => '',
			'storagePage' => '',
			'mapping' => [],
		], $this->settings);
		if (!$this->settings['table']) {
			return;
		}
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable($this->settings['table']);
		$values = [
			'pid' => (int)($this->settings['storagePage'] ?: $this->request->getAttribute('currentContentObject')?->data['pid'] ?? $this->formRecord->getPid()),
		];

		foreach ($this->settings['mapping'] as $column => $field) {
			$values[$column] = $this->formValues[$field] ?? '';
		}
		$queryBuilder->insert($this->settings['table'])
			->values($values)
			->executeQuery();
	}
}
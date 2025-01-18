<?php

namespace UBOS\Puck\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class SaveSubmissionFinisher extends AbstractFinisher
{
	const TABLE_NAME = 'tx_formal_form_submission';
	public function execute(): ?ResponseInterface
	{
		$this->settings = array_merge([
			'storagePage' => '',
		], $this->settings);
		$queryBuilder = GeneralUtility::makeInstance(Core\Database\ConnectionPool::class)
			->getQueryBuilderForTable(self::TABLE_NAME);
		$queryBuilder->insert(self::TABLE_NAME)
			->values([
				'form' => $this->formRecord->getUid(),
				'form_values' => json_encode($this->formValues),
				'plugin' => $this->request->getAttribute('currentContentObject')?->data['uid'],
				'pid' => (int)($this->settings['storagePage'] ?: $this->request->getAttribute('currentContentObject')?->data['pid'] ?? $this->formRecord->getPid()),
				'crdate' => time(),
				'tstamp' => time(),
			])
			->executeQuery();
		return null;
	}
}
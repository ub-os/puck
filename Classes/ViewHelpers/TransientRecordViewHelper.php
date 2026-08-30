<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class TransientRecordViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('data', 'array', '', true);
		$this->registerArgument('type', 'string', '', true);
		$this->registerArgument('set', 'string', '', false, '');
	}

	public function render(): ?RecordInterface
	{
		$data = $this->arguments['data'];
		$data['uid'] = $data['uid'] ?? 0;
		$data['pid'] = $data['pid'] ?? 0;

		$rawRecord = new RawRecord(
			$data['uid'],
			$data['pid'],
			$data,
			new ComputedProperties(),
			$this->arguments['type']
		);
		$record = new Record(
			$rawRecord,
			$data
		);
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $record);
			return null;
		}
		return $record;
	}
}

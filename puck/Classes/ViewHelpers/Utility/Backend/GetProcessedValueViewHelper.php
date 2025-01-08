<?php


namespace UBOS\Puck\ViewHelpers\Utility\Backend;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetProcessedValueViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('table', 'string', '', true);
		$this->registerArgument('col', 'string', '', true);
		$this->registerArgument('value', 'string', '', true);
	}

	public function render(): string
	{
		return '<b>' . BackendUtility::getItemLabel($this->arguments['table'], $this->arguments['col']) . '</b> ' .
			BackendUtility::getProcessedValue($this->arguments['table'], $this->arguments['col'], $this->arguments['value']);
	}
}


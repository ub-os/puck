<?php


namespace UBOS\Puck\ViewHelpers\Utility\Backend;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ResolveFileReferencesViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('row', 'array', '', true);
		$this->registerArgument('table', 'string', '', true);
		$this->registerArgument('field', 'string', '', true);
	}

	public function render()
	{
		return BackendUtility::resolveFileReferences($this->arguments['table'], $this->arguments['field'], $this->arguments['row']);
	}
}


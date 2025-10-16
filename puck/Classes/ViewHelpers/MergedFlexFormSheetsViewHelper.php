<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Domain\FlexForm\FlexFormFieldValues;

class MergedFlexFormSheetsViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('value', FlexFormFieldValues::class, '', true);
	}

	public function render(): ?array
	{
		/** @var ?FlexFormFieldValues $fieldValues */
		$fieldValues = $this->arguments['value'] ?: $this->renderChildren() ?? null;
		if ($fieldValues instanceof FlexFormFieldValues) {
			return null;
		}
		$sheets = $fieldValues->toArray();
		$mergedArray = [];
		foreach ($sheets as $sheet) {
			$mergedArray = array_merge_recursive($mergedArray, $sheet);
		}
		return $mergedArray;
	}
}
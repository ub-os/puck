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
		/** @var ?FlexFormFieldValues $value */
		$value = $this->renderChildren();
		$sheets = $value->toArray();
		$mergedArray = [];
		foreach ($sheets as $sheet) {
			$mergedArray = array_merge_recursive($mergedArray, $sheet);
		}
		return $mergedArray;
	}

	public function getContentArgumentName(): ?string
	{
		return 'value';
	}
}
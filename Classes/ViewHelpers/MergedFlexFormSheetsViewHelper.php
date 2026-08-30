<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;

class MergedFlexFormSheetsViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('value', FlexFormFieldValues::class, '', true);
	}

	public function render(): ?array
	{
		$value = $this->renderChildren();
		if (!$value instanceof FlexFormFieldValues) {
			return null;
		}
		$mergedArray = [];
		foreach ($value->toArray() as $sheet) {
			$mergedArray = array_replace_recursive($mergedArray, $sheet);
		}
		return $mergedArray;
	}

	public function getContentArgumentName(): ?string
	{
		return 'value';
	}
}
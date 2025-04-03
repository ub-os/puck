<?php

namespace UBOS\Puck\UserFunctions\FormEngine;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait for ItemsProcFunc classes
 */
trait ItemsProcFuncUtilsTrait
{

	/**
	 * @param array $items
	 * @param string $allowedValues
	 * @return array
	 */
	protected function filterItemsByValues(array $items, string $allowedValues): array
	{
		return array_filter($items, function ($item) use ($allowedValues) {
			return GeneralUtility::inList($allowedValues, $item[1]);
		});
	}

	// workaround for issue: sometimes field values ($params['row'][$fieldName]) are nested as the first element of an array

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function getValueFromArrayOrValue(mixed $value): mixed
	{
		if (!is_array($value)) {
			return $value;
		}
		if (isset($value[0])) {
			return $value[0];
		}
		return null;
	}

	// shorthand for getValueFromArrayOrValue

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	protected function val(mixed $value): mixed
	{
		return $this->getValueFromArrayOrValue($value);
	}

}

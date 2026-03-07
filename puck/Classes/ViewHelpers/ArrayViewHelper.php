<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to manipulate arrays in Fluid templates
 *
 * This ViewHelper provides various array manipulation operations that can be applied
 * to input arrays in Fluid templates. Multiple operations can be chained together.
 *
 * Example usage:
 * <u:array input="{someArray}" filter="{type: 'page'}" set="filteredPages" />
 * <u:array set="rangeArray" range="4 12 2" />
 * {someArray -> u:array(push: {newItem}, merge: {anotherArray})}
 * {someArray -> u:array(push: {newItem}) -> u:array(merge: {anotherArray}) -> u:array(implode: ', ', set: 'result')}
 */
class ArrayViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('input', 'array', 'Input array to manipulate. If not provided, child content will be used', false, []);
		$this->registerArgument('bulk', 'bool', 'Apply operations to each item in the array separately', false, false);
		$this->registerArgument('changeKeys', 'array', 'Map of old keys to new keys', false);
		$this->registerArgument('indexKey', 'string', 'Reindex array of associative arrays with value of this key', false);
		$this->registerArgument('keys', 'bool', 'Return array keys instead of values', false);
		$this->registerArgument('slice', 'string', 'Slice array (format: "offset ?length")', false);
		$this->registerArgument('push', 'mixed', 'Add value to end of array', false);
		$this->registerArgument('merge', 'array', 'Merge array with another array', false);
		$this->registerArgument('mergeRecursive', 'array', 'Recursively merge array with another array', false);
		$this->registerArgument('range', 'string', 'Create range array (format: "?start end ?step")', false);
		$this->registerArgument('unset', 'string', 'Remove keys from array (space-separated list)', false);
		$this->registerArgument('search', 'string', 'Find key for a value in the array', false);
		$this->registerArgument('filter', 'array', 'Filter array by key-value pairs (items must match all conditions)', false);
		$this->registerArgument('inverseFilter', 'array', 'Inverse filter (items must not match any condition)', false);
		$this->registerArgument('implode', 'string', 'Join array values with specified string', false);
		$this->registerArgument('if', 'boolean', 'Condition to determine if operations should be applied', false);
		$this->registerArgument('getValue', 'string', 'Get a value from the array by key', false);
		$this->registerArgument('set', 'string', 'Variable name to store result in (no output)', false, '');
		$this->registerArgument('operations', 'string', 'Space-separated list of operations to apply in order', false, 'unset changeKeys merge mergeRecursive range indexKey keys push slice filter inverseFilter search getValue implode');
	}

	/**
	 * Processes the array with configured operations
	 *
	 * @return array|string|null Processed array or string (if implode used), or null if result stored in variable
	 */
	public function render(): array|string|null
	{
		$input = $this->arguments['input'] ?: $this->renderChildren() ?? [];
		$result = ArrayViewHelper::process($input, $this->arguments);
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}

	/**
	 * Processes input array based on provided arguments
	 *
	 * @param array $input The input array
	 * @param array $arguments ViewHelper arguments
	 * @return array|string|null The processed result
	 */
	protected static function process(array $input, array $arguments): array|string|null
	{
		if ($arguments['if'] !== null && !$arguments['if']) {
			return $input;
		}
		if ($arguments['bulk']) {
			return array_map(function ($value) use ($arguments) {
				return self::doOperations($value, $arguments);
			}, $input);
		} else {
			return self::doOperations($input, $arguments);
		}
	}

	/**
	 * Applies configured operations to the array in order
	 *
	 * @param array $array The array to process
	 * @param array $arguments ViewHelper arguments
	 * @return array|string The processed array or string
	 */
	protected static function doOperations(array $array, array $arguments): array|string
	{
		$operations = explode(' ', $arguments['operations']);
		foreach ($operations as $operation) {
			if ($arguments[$operation] !== null) {
				$array = self::$operation($array, $arguments[$operation]);
			}
		}
		return $array;
	}

	/**
	 * Renames keys in an array
	 *
	 * @param array $array Input array
	 * @param array $changeKeys Map of old keys to new keys
	 * @return array Array with renamed keys
	 */
	protected static function changeKeys(array $array, array $changeKeys): array
	{
		foreach ($changeKeys as $oldKey => $newKey) {
			if (isset($array[$oldKey])) {
				$array[$newKey] = $array[$oldKey];
				unset($array[$oldKey]);
			}
		}
		return $array;
	}

	/**
	 * Gets the keys of an array
	 *
	 * @param array $array Input array
	 * @param bool $keys Whether to return keys
	 * @return array Array of keys or unchanged array
	 */
	protected static function keys(array $array, $keys): array
	{
		if ($keys === true) {
			return array_keys($array);
		}
		return $array;
	}

	/**
	 * Slices an array
	 *
	 * @param array $array Input array
	 * @param string $slice Slice parameters (format: "offset length")
	 * @return array Sliced array
	 */
	protected static function slice(array $array, string $slice): array
	{
		$slice = explode(' ', $slice);
		if (count($slice) === 1) {
			$array = array_slice($array, (int)$slice[0]);
		} else if (count($slice)) {
			$array = array_slice($array, (int)$slice[0], (int)$slice[1]);
		}
		return $array;
	}

	/**
	 * Merges two arrays
	 *
	 * @param array $array Base array
	 * @param array $merge Array to merge in
	 * @return array Merged array
	 */
	protected static function merge(array $array, array $merge): array
	{
		return array_merge($array, $merge);
	}

	/**
	 * Recursively merges two arrays
	 *
	 * @param array $array Base array
	 * @param array $mergeRecursive Array to merge in recursively
	 * @return array Recursively merged array
	 */
	protected static function mergeRecursive(array $array, array $mergeRecursive): array
	{
		return ArrayUtility::mergeRecursiveWithOverrule($array, $mergeRecursive);
	}

	/**
	 * Creates a range array
	 *
	 * @param array $array Input array (will be replaced)
	 * @param string $range Range parameters (format: "?start end ?step")
	 * @return array Range array
	 */
	protected static function range(array $array, string $range): array
	{
		$range = explode(' ', $range);
		if (count($range) === 1) {
			$array = range(0, (int)$range[0]);
		} else if (count($range) === 2) {
			$array = range((int)$range[0], (int)$range[1]);
		} else if (count($range)) {
			$array = range((int)$range[0], (int)$range[1], (int)$range[2]);
		}
		return $array;
	}

	/**
	 * Removes elements from array by key
	 *
	 * @param array $array Input array
	 * @param string $unset Space-separated list of keys to remove
	 * @return array Array with keys removed
	 */
	protected static function unset(array $array, string $unset): array
	{
		$unset = explode(' ', $unset);
		foreach ($unset as $key) {
			unset($array[$key]);
		}
		return $array;
	}

	/**
	 * Joins array elements with a string
	 *
	 * @param array $array Input array
	 * @param string $implode Glue string to join with
	 * @return string Joined string
	 */
	protected static function implode(array $array, string $implode): string
	{
		return implode($implode, $array);
	}

	/**
	 * Reindexes array using a field from each item as the key
	 *
	 * @param array $array Input array of arrays/objects
	 * @param string $key Field name to use as index
	 * @return array Reindexed array
	 */
	protected static function indexKey(array $array, string $key): array
	{
		if (!$key) {
			return $array;
		}
		$arr = [];
		foreach ($array as $value) {
			$arr[$value[$key]] = $value;
		}
		return $arr;
	}

	/**
	 * Adds a value to the end of an array
	 *
	 * @param array $array Input array
	 * @param mixed $push Value to add
	 * @return array Array with new value added
	 */
	protected static function push(array $array, $push): array
	{
		$array[] = $push;
		return $array;
	}

	/**
	 * Searches for a value in the array
	 *
	 * @param array $array Input array
	 * @param string $search Value to search for
	 * @return array Key of the first matching value
	 */
	protected static function search(array $array, string $search): array
	{
		return array_search($search, $array);
	}

	protected static function getValue(array $array, string $key): mixed
	{
		return $array[$key] ?? null;
	}

	/**
	 * Filters array items that match all criteria
	 *
	 * @param array $array Input array of arrays/objects
	 * @param array $filter Key-value pairs that items must match
	 * @return array Filtered array
	 */
	protected static function filter(array $array, array $filter): array
	{
		$array = array_filter($array, function ($value) use ($filter) {
			foreach ($filter as $key => $val) {
				if ($value[$key] !== $val) {
					return false;
				}
			}
			return true;
		});
		return $array;
	}

	/**
	 * Filters array items that do not match any criteria
	 *
	 * @param array $array Input array of arrays/objects
	 * @param array $filter Key-value pairs that items must not match
	 * @return array Filtered array
	 */
	protected static function inverseFilter(array $array, array $filter): array
	{
		$array = array_filter($array, function ($value) use ($filter) {
			foreach ($filter as $key => $val) {
				if ($value[$key] === $val) {
					return false;
				}
			}
			return true;
		});
		return $array;
	}
}
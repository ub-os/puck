<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for string manipulation operations
 *
 * Provides various string operations like searching, replacing, exploding,
 * and template-based substitution. Multiple operations can be chained together.
 *
 * Example usage:
 * <u:string input="Hello World" search="Hello" replace="Hi" />
 * <u:string input="item1,item2,item3" explode="," set="items" />
 * <u:string dataReplace="{name: 'Jane'}" input="Hello {{name}}" />
 */
class StringViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('input', 'mixed', '', false, '');
		$this->registerArgument('bulk', 'bool', '', false, false);
		$this->registerArgument('contains', 'string', '', false);
		$this->registerArgument('search', 'string', '', false);
		$this->registerArgument('replace', 'string', '', false);
		$this->registerArgument('dataReplace', 'array', '', false);
		$this->registerArgument('explode', 'string', '', false);
		$this->registerArgument('length', 'string', '', false);
		$this->registerArgument('if', 'boolean', '', false);
		$this->registerArgument('set', 'string', '', false, '');
		$this->registerArgument('operations', 'string', '', false, 'contains search dataReplace length explode');
	}

	/**
	 * Performs string operations based on the provided arguments
	 *
	 * @return array|string|null|int The processed result, which varies based on operations performed
	 */
	public function render(): mixed
	{
		$input = $this->arguments['input'];
		if ($input === null || $input === '') {
			$input = $this->renderChildren();
		}
		if (!is_string($input) && !is_array($input)) {
			$input = (string)$input;
		}
		$result = self::process($input, $this->arguments);
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}

	/**
	 * Processes input based on provided arguments
	 *
	 * @param array|string $input The input to process
	 * @param array $arguments ViewHelper arguments
	 * @return array|string|null The processed result
	 */
	protected static function process(array|string $input, array $arguments): mixed
	{
		if ($arguments['if'] !== null && !$arguments['if']) {
			return $input;
		}
		if ($arguments['bulk'] && is_array($input)) {
			return array_map(
				static fn($value) => self::doOperations(is_string($value) ? $value : (string)$value, $arguments),
				$input
			);
		}
		return self::doOperations(is_array($input) ? '' : $input, $arguments);
	}

	private const ALLOWED_OPERATIONS = ['contains', 'search', 'dataReplace', 'length', 'explode'];

	/**
	 * Applies the configured operations to the string in order. Stops as soon as
	 * an operation produces a non-string (contains / length / explode).
	 */
	protected static function doOperations(string $string, array $arguments): mixed
	{
		$result = $string;
		foreach (explode(' ', $arguments['operations']) as $operation) {
			if (!in_array($operation, self::ALLOWED_OPERATIONS, true) || ($arguments[$operation] ?? null) === null) {
				continue;
			}
			if (!is_string($result)) {
				// a previous operation already produced a non-string (bool/int/array)
				break;
			}
			$result = self::$operation($result, $arguments[$operation], $arguments);
		}
		return $result;
	}

	/**
	 * Checks if a string contains a substring
	 *
	 * @param string $string The string to check
	 * @param string $contains The substring to look for
	 * @return bool True if the substring is found
	 */
	protected static function contains(string $string, string $contains): bool
	{
		return str_contains($string, $contains);
	}

	/**
	 * Gets the length of a string
	 *
	 * @param string $string The string to measure
	 * @param string $length Unused parameter (for consistency with other operations)
	 * @return int The length of the string
	 */
	protected static function length(string $string, string $length): int
	{
		return strlen($string);
	}

	/**
	 * Replaces all occurrences of a search string with a replacement
	 *
	 * @param string $string The string to search in
	 * @param string $search The string to search for
	 * @param array $args Arguments containing 'replace' key with replacement string
	 * @return bool|string The resulting string with replacements
	 */
	protected static function search(string $string, string $search, array $args): bool|string
	{
		return str_replace($search, $args['replace'], $string);
	}

	/**
	 * Performs template-based substitution with variables in {{var}} format
	 *
	 * Replaces variables in the format {{variable.path}} with values from
	 * the dataReplace array. Supports nested paths with dot notation.
	 *
	 * @param string $string The template string containing {{var}} placeholders
	 * @param array $dataReplace Array of replacement values
	 * @return string The string with replacements made
	 */
	protected static function dataReplace(string $string, array $dataReplace): string
	{
		preg_match_all('/\{\{(.*?)\}\}/', $string, $matches);
		foreach (array_unique($matches[1]) as $token) {
			$value = $dataReplace;
			foreach (explode('.', trim($token)) as $part) {
				if (!is_array($value) || !array_key_exists($part, $value)) {
					$value = null;
					break;
				}
				$value = $value[$part];
			}
			if (is_scalar($value)) {
				$string = str_replace('{{' . $token . '}}', (string)$value, $string);
			}
		}

		return $string;
	}

	/**
	 * Splits a string into an array using a delimiter
	 *
	 * @param string $string The string to split
	 * @param string $explode The delimiter to split by
	 * @return array The resulting array of substrings
	 */
	protected static function explode(string $string, string $explode): array
	{
		return explode($explode, $string);
	}
}
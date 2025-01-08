<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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

	public function render(): array|string|null|int
	{
		$input = $this->arguments['input'] ?: $this->renderChildren() ?? '';
		$result = self::process($input, $this->arguments);
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}

	protected static function process(array|string $input, array $arguments): array|string|null
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

	protected static function doOperations(string $string, array $arguments): string|array
	{
		$operations = explode(' ', $arguments['operations']);
		foreach ($operations as $operation) {
			if ($arguments[$operation] !== null) {
				$string = self::$operation($string, $arguments[$operation], $arguments);
			}
		}
		return $string;
	}

	protected static function contains(string $string, string $contains): bool
	{
		return str_contains($string, $contains);
	}

	protected static function length(string $string, string $length): int
	{
		return strlen($string);
	}

	protected static function search(string $string, string $search, array $args): bool|string
	{
		return str_replace($search, $args['replace'], $string);
	}

	protected static function dataReplace(string $string, array $dataReplace): string
	{
		preg_match_all("/\{\{(.*?)\}\}/", $string, $matches);
		$matches = array_values(array_unique($matches, SORT_REGULAR));
		$variables = array_map(function ($match) {
			return [
				"match" => '{{' . $match . '}}',
				"parts" => explode(".", trim($match))
			];
		}, $matches[1]);

		foreach ($variables as $variable) {
			$value = $dataReplace;
			foreach ($variable["parts"] as $part) {
				if (isset($value[$part])) {
					$value = $value[$part];
				} else {
					$value = null;
					break;
				}
			}
			if ($value !== null) {
				$string = str_replace($variable["match"], $value, $string);
			}
		}

		return $string;
	}

	protected static function explode(string $string, string $explode): array
	{
		return explode($explode, $string);
	}
}

<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Builds a class string from a base class, a map of conditionally-included
 * classes, and BEM modifiers — the same logic tag:x uses for its class
 * handling, available standalone for ViewHelpers/components that aren't
 * tag:x-based.
 *
 * {u:class(base: '{class} {name}', list: {active: isActive}, modifiers: {layout: record.layout})}
 */
class ClassViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('base', 'string', 'Unconditional class(es)', false, '');
		$this->registerArgument('list', 'array', 'Map of className => bool, truthy keys included', false, []);
		$this->registerArgument('modifiers', 'array', 'BEM modifiers as key => value pairs', false, []);
	}

	public function render(): string
	{
		return self::build($this->arguments['base'], $this->arguments['list'], $this->arguments['modifiers']);
	}

	public static function build(string $base, array $list, array $modifiers): string
	{
		$parts = array_filter([
			trim($base),
			...array_keys(array_filter($list)),
			trim(self::renderModifiers($modifiers)),
		], static fn ($part) => $part !== '');
		return implode(' ', $parts);
	}

	/**
	 * Renders BEM modifiers: `true` renders as `key`, any other non-empty
	 * value renders as `key-value` (including 0, 1, '0', '1' — use classList
	 * for plain on/off toggles instead). `null`, `false`, and `''` are skipped.
	 */
	public static function renderModifiers(array $modifiers): string
	{
		$parts = [];
		foreach ($modifiers as $key => $value) {
			if ($value === null || $value === false || $value === '') {
				continue;
			}
			$parts[] = $value === true ? $key : $key . '-' . $value;
		}
		return implode(' ', $parts);
	}
}
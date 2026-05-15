<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for generating BEM (Block Element Modifier) CSS classes
 *
 * This ViewHelper helps to consistently generate CSS class names
 * following the BEM (Block Element Modifier) naming convention.
 *
 * Example usage:
 * {u:bem(block:'button', mod:{size:'large', active:true, disabled:false })}
 * Outputs: button -size-large -active
 *
 * "block" argument can be omitted if "block" or "name" variable is set in template
 * {u:bem(el:'header')}
 * Outputs: card__header
 */
class BemViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('block', 'string', 'The BEM block name. If not provided, looks for "block" or "name" variables in the template', false, '');
		$this->registerArgument('el', 'string', 'The BEM element name', false, '');
		$this->registerArgument('mod', 'array', 'Array of modifiers as key-value pairs', false, []);
		$this->registerArgument('raw', 'string', 'Additional raw class names to append', false, '');
	}

	/**
	 * Generates BEM class string based on the provided arguments
	 *
	 * Uses the following pattern:
	 * - block: The main component name
	 * - element: Separated by double underscore (__) from block
	 * - modifiers: Separated by hyphen (-) from block+element
	 * - Boolean modifiers use only the key
	 *
	 * @return string Generated BEM class string
	 */
	public function render(): string
	{
		$block = $this->arguments['block'] ?:
			$this->renderingContext->getVariableProvider()->get('block') ?:
				$this->renderingContext->getVariableProvider()->get('name') ?:
					'';
		return trim($block
			. ($this->arguments['el'] ? '__' . $this->arguments['el'] : '')
			. self::renderModifiers($this->arguments['mod'])
			. ' ' . $this->arguments['raw']);
	}

	/**
	 * Renders modifier classes from an array of modifiers
	 *
	 * Handles boolean modifiers (just the key) and value modifiers (key-value).
	 * Values like true, 1, or "1" will be treated as boolean modifiers.
	 * A value of "default" will be skipped.
	 *
	 * @param array $modifiers Associative array of modifiers
	 * @return string Space-separated string of modifier classes
	 */
	protected static function renderModifiers(array $modifiers): string
	{
		$result = '';
		foreach ($modifiers as $key => $value) {
			if ($value && $value !== 'default') {
				if ($value === '1' || $value === 1 || $value === true) {
					$result .= ' -' . $key;
				} else {
					$result .= ' -' . $key . '-' . $value;
				}
			}
		}
		return $result;
	}
}
<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for variable operations in Fluid templates
 *
 * Provides utility functions for setting and retrieving variables,
 * as well as basic conditional logic for variable assignment.
 *
 * Example usage:
 * <u:var value="{someValue}" set="myVar" /> <!-- Stores value in myVar -->
 * <u:var if="{condition}" then="Yes" else="No" set="result" /> <!-- Conditional assignment -->
 * <u:var get="{myVar}" /> <!-- Outputs myVar value -->
 */
class VarViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('value', 'mixed', '', false, null);
		$this->registerArgument('if', 'boolean', '', false, false);
		$this->registerArgument('then', 'mixed', '', false, null);
		$this->registerArgument('else', 'mixed', '', false, null);
		$this->registerArgument('get', 'mixed', '', false, null);
		$this->registerArgument('fallback', 'mixed', '', false, null);
		$this->registerArgument('set', 'string', '', false, '');
	}

	/**
	 * Performs variable operations based on the provided arguments
	 *
	 * The ViewHelper can:
	 * 1. Store a value in a variable using 'set'
	 * 2. Apply conditional logic with 'if', 'then', 'else'
	 * 3. Output a specified value with 'get'
	 *
	 * If no operation is specified, it returns null.
	 *
	 * @return mixed The value from 'get', the child content, or null
	 */
	public function render(): mixed
	{
		// "value" and "get" are interchangeable value sources
		$value = $this->arguments['value'] ?? $this->arguments['get'];
		if ($value === null) {
			$rendered = $this->renderChildren();
			$value = ($rendered === '' || $rendered === null) ? null : $rendered;
		}

		// conditional mode: active as soon as "then" or "else" is given
		if ($this->arguments['then'] !== null || $this->arguments['else'] !== null) {
			$value = $this->arguments['if'] ? $this->arguments['then'] : $this->arguments['else'];
		}

		if ($value === null) {
			$value = $this->arguments['fallback'];
		}

		if ($this->arguments['set'] !== '') {
			if ($value !== null) {
				$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $value);
			}
			return null;
		}
		return $value;
	}
}
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
		$value = $this->arguments['value'] ?: $this->renderChildren() ?? null;
		if ($this->arguments['if'] && $this->arguments['then'] !== null) {
			$value = $this->arguments['then'];
		} elseif ($this->arguments['else'] !== null) {
			$value = $this->arguments['else'];
		}
		if ($value === null) {
			$value = $this->arguments['fallback'];
		}
		if ($value !== null && $this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $value);
		}
		if ($this->arguments['get'] === '') {
			return $value;
		}
		if ($this->arguments['get'] !== null) {
			return $this->arguments['get'];
		}
		return null;
	}
}
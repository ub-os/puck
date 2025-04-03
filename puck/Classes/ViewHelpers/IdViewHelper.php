<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for generating unique IDs within templates
 *
 * Provides auto-incrementing unique identifiers that can be used for HTML elements
 * or other components requiring unique IDs. The ID format is 'x{counter}[-{suffix}]'.
 *
 * Example usage:
 * <u:id /> <!-- Outputs: x0 -->
 * <u:id /> <!-- Outputs: x1 -->
 * <u:id suffix="button" /> <!-- Outputs: x2-button -->
 * <u:id set="modalId" /> <!-- Stores "x3" in variable "modalId" -->
 */
class IdViewHelper extends AbstractViewHelper
{
	/**
	 * Prefix used for all generated IDs
	 */
	protected const PREFIX = 'x';

	/**
	 * Static counter for auto-incrementing IDs
	 */
	protected static int $idx = 0;

	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('suffix', 'string', 'Optional suffix to append to the ID for better readability', false, '');
		$this->registerArgument('set', 'string', 'Variable name to store the generated ID in', false, '');
	}

	/**
	 * Generates a unique ID
	 *
	 * Creates an ID in the format 'x{counter}[-{suffix}]' where counter is an incrementing number.
	 * IDs are unique within a single page request.
	 *
	 * @return string|null Generated ID or null if stored in variable
	 */
	public function render(): ?string
	{
		$result = self::PREFIX . self::$idx++ . ($this->arguments['suffix'] ? '-' . $this->arguments['suffix'] : '');
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}
}
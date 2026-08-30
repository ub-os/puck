<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to render HTML attributes from an array
 *
 * Converts an associative array of key-value pairs into HTML attribute strings.
 * Supports key replacements for special characters not allowed in Fluid array keys.
 *
 * Example usage:
 * <u:renderAttributes attributes="{class: 'btn', id: 'submit'}" />
 * <!-- Outputs: class="btn" id="submit" -->
 *
 * <u:renderAttributes attributes="{data____foo: 'bar'}" keyReplacements={"____": ":"} />
 * <!-- Outputs: data:foo="bar" -->
 */
class RenderAttributesViewHelper extends AbstractViewHelper
{
	protected $escapeOutput = false;

	public function initializeArguments(): void
	{
		$this->registerArgument('attributes', 'array', '');
		$this->registerArgument('keyReplacements', 'array', '', false, []);
	}

	/**
	 * Renders an array of attributes as HTML attribute string
	 *
	 * Takes an associative array and converts each key-value pair into
	 * an HTML attribute string. Also handles key replacements for characters
	 * that aren't allowed in Fluid array keys.
	 *
	 * @return string Space-separated attribute string (key="value")
	 */
	public function render(): string
	{
		$attributes = $this->arguments['attributes'];
		if (!is_array($attributes) || $attributes === []) {
			$rendered = $this->renderChildren();
			$attributes = is_array($rendered) ? $rendered : [];
		}
		$replacements = $this->arguments['keyReplacements'] ?: [];
		$string = '';
		foreach ($attributes as $key => $value) {
			if ($value === null || $value === false) {
				continue;
			}
			if ($replacements) {
				// e.g. "data____foo" => "data:foo", since ":" is not allowed in fluid array keys
				$key = str_replace(array_keys($replacements), array_values($replacements), (string)$key);
			}
			$string .= htmlspecialchars((string)$key, ENT_QUOTES)
				. '="' . htmlspecialchars((string)$value, ENT_QUOTES) . '" ';
		}
		return $string;
	}
}

<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class AttributesViewHelper extends AbstractViewHelper
{
	protected $escapeOutput = false;

	public function initializeArguments(): void
	{
		$this->registerArgument('attributes', 'mixed', '');
	}

	public function render(): string
	{
		$attributes = $this->arguments['attributes'] ?: $this->renderChildren() ?? [];
		if (is_string($attributes)) {
			return $attributes;
		}
		if (is_array($attributes)) {
			$string = '';
			foreach ($attributes as $key => $value) {
				$string .= $key . '="' . $value . '" ';
			}
			return $string;
		}
		return '';
	}
}

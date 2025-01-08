<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class BemViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('block', 'string', '', false, '');
		$this->registerArgument('el', 'string', '', false, '');
		$this->registerArgument('mod', 'array', '', false, []);
		$this->registerArgument('raw', 'string', '', false, '');
	}

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

<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PrependClassViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('class', 'string', 'The CSS class(es) to prepend', false, '');
	}

	public function render(): string
	{
		$class = $this->arguments['class'] ?: $this->renderChildren() ?: $this->renderingContext->getVariableProvider()->get('class') ?? '';
		return $class ? $class . ' ' : '';
	}
}
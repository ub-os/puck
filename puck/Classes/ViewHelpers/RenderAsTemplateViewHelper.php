<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderAsTemplateViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('content', 'string', '', true);
	}

	public function render(): array
	{
		$parser = $this->renderingContext->getTemplateParser();
		return html_entity_decode($parser->parse($this->arguments['content'])->render(), ENT_QUOTES, 'UTF-8');
	}

}

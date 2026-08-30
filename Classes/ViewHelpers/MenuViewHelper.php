<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use UBOS\Puck\Menu\MenuProcessor;

/**
 * ViewHelper for generating different types of menus
 *
 * Uses B13's menu processors to create menus like page trees, language selectors,
 * breadcrumbs, and page lists. Provides a simplified interface to the processors.
 *
 * Example usage:
 * <u:menu processor="tree" pages="1,2,3" depth="2" set="treeMenu" />
 * <u:menu processor="language" excludeLanguages="1" set="langMenu"/>
 * {u:menu(processor:"breadcrumbs")}
 * {u:menu(pages:"12", processMedia:1)} />
 */
class MenuViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('pages', 'string', '', false, '1');
		$this->registerArgument('depth', 'integer', '', false, 1);
		$this->registerArgument('processor', 'string', '', false, 'list');
		$this->registerArgument('excludePages', 'string', '', false, '');
		$this->registerArgument('includeNotInMenu', 'boolean', '', false, null);
		$this->registerArgument('excludeLanguages', 'string', '', false, '');
		$this->registerArgument('addAllSiteLanguages', 'boolean', '', false, false);
		$this->registerArgument('excludeDoktypes', 'string', '', false, '199,254,255');
		$this->registerArgument('processMedia', 'boolean', '', false, false);
		$this->registerArgument('set', 'string', '', false, '');
	}

	/**
	 * Generates menu data using one of B13's menu processors (tree, language,
	 * breadcrumbs, list) based on the 'processor' argument.
	 *
	 * @return array<mixed>|null Menu data array, or null when stored in a variable via "set"
	 */
	public function render(): ?array
	{
		$result = GeneralUtility::makeInstance(MenuProcessor::class)->process(
			$this->arguments['processor'],
			[
				'pages' => $this->arguments['pages'],
				'depth' => $this->arguments['depth'],
				'excludePages' => $this->arguments['excludePages'],
				'includeNotInMenu' => $this->arguments['includeNotInMenu'],
				'excludeLanguages' => $this->arguments['excludeLanguages'],
				'addAllSiteLanguages' => $this->arguments['addAllSiteLanguages'],
				'excludeDoktypes' => $this->arguments['excludeDoktypes'],
				'processMedia' => $this->arguments['processMedia'],
			]
		);
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
			return null;
		}
		return $result;
	}
}

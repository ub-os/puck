<?php

namespace UBOS\Puck\Components;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class IconComponentCollection extends AbstractComponentCollection
{
	public function getTemplatePaths(): TemplatePaths
	{
		$templatePaths = new TemplatePaths();
		$templatePaths->setTemplateRootPaths([
			ExtensionManagementUtility::extPath('puck', 'Resources/Private/FluidComponents/Icons/')
		]);
		return $templatePaths;
	}
	protected function additionalArgumentsAllowed(string $viewHelperName): bool
	{
		return true;
	}
	public function resolveTemplateName(string $viewHelperName): string
	{
		$fragments = array_map(ucfirst(...), explode('.', $viewHelperName));
		return implode('/', $fragments);
	}
}
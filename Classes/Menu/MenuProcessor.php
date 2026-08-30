<?php

namespace UBOS\Puck\Menu;

use B13\Menus\DataProcessing\BreadcrumbsMenu;
use B13\Menus\DataProcessing\LanguageMenu;
use B13\Menus\DataProcessing\ListMenu;
use B13\Menus\DataProcessing\TreeMenu;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Runs one of B13's menu data processors (tree / list / language / breadcrumbs)
 * and returns the resulting menu array.
 *
 * Shared by MenuViewHelper and SysNavigationDataViewHelper.
 */
final class MenuProcessor
{
	private const MENU_KEY = 'menu';

	/**
	 * @param array{
	 *     pages?: string,
	 *     depth?: int,
	 *     excludePages?: string,
	 *     includeNotInMenu?: bool|null,
	 *     excludeLanguages?: string,
	 *     addAllSiteLanguages?: bool,
	 *     excludeDoktypes?: string,
	 *     processMedia?: bool
	 * } $options
	 * @return array<mixed>
	 */
	public function process(string $type, array $options): array
	{
		$doktypes = $options['excludeDoktypes'] ?? '199,254,255';
		$configuration = match ($type) {
			'tree' => [
				'as' => self::MENU_KEY,
				'entryPoints' => $options['pages'] ?? '1',
				'depth' => $options['depth'] ?? 1,
				'excludePages' => $options['excludePages'] ?? '',
				'includeNotInMenu' => $options['includeNotInMenu'] ?? false,
				'excludeDoktypes' => $doktypes,
			],
			'language' => [
				'as' => self::MENU_KEY,
				'includeNotInMenu' => $options['includeNotInMenu'] ?? true,
				'excludeLanguages' => $options['excludeLanguages'] ?? '',
				'addAllSiteLanguages' => $options['addAllSiteLanguages'] ?? false,
			],
			'breadcrumbs' => [
				'as' => self::MENU_KEY,
				'excludePages' => $options['excludePages'] ?? '',
				'includeNotInMenu' => $options['includeNotInMenu'] ?? false,
				'excludeDoktypes' => $doktypes,
			],
			default => [
				'as' => self::MENU_KEY,
				'pages' => $options['pages'] ?? '1',
				'includeNotInMenu' => $options['includeNotInMenu'] ?? false,
				'excludeDoktypes' => $doktypes,
			],
		};
		$dataProcessor = match ($type) {
			'tree' => GeneralUtility::makeInstance(TreeMenu::class),
			'language' => GeneralUtility::makeInstance(LanguageMenu::class),
			'breadcrumbs' => GeneralUtility::makeInstance(BreadcrumbsMenu::class),
			default => GeneralUtility::makeInstance(ListMenu::class),
		};

		$configuration = GeneralUtility::makeInstance(TypoScriptService::class)
			->convertPlainArrayToTypoScriptArray($configuration);
		if ($options['processMedia'] ?? false) {
			$configuration['dataProcessing.'] = [
				'10' => 'TYPO3\CMS\Frontend\DataProcessing\FilesProcessor',
				'10.' => [
					'references.' => [
						'table' => 'pages',
						'fieldName' => 'media',
					],
					'as' => 'processedMedia',
				],
			];
		}

		$processed = $dataProcessor->process(
			GeneralUtility::makeInstance(ContentObjectRenderer::class),
			[],
			$configuration,
			[]
		);
		$menu = $processed[self::MENU_KEY] ?? [];
		return is_array($menu) ? $menu : [];
	}
}

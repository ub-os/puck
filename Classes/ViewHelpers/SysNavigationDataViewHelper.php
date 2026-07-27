<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use B13\Menus\DataProcessing\ListMenu;
use B13\Menus\DataProcessing\TreeMenu;

/**
 *
 */
class SysNavigationDataViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('identifier', 'string', '', true);
		$this->registerArgument('depth', 'integer', '', false, 1);
		$this->registerArgument('excludePages', 'string', '', false, '');
		$this->registerArgument('includeNotInMenu', 'boolean', '', false, null);
		$this->registerArgument('excludeDoktypes', 'string', '', false, '199,254,255');
		$this->registerArgument('processMedia', 'boolean', '', false, false);
		$this->registerArgument('set', 'string', '', false, '');
	}

	public function render(): ?array
	{
		$identifier = $this->arguments['identifier'];

		$navElement = $this->getNavigationElementByIdentifier($identifier);
		if (!$navElement) {
			return null;
		}

		$data = [
			'identifier' => $identifier,
			'label' => $navElement['header'],
			'mode' => $navElement['layout'],
			'pages' => $navElement['pages'],
		];

		$contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
		$as = 'menu';
		switch ($data['mode']) {
			case 'tree':
				$dataProcessor = GeneralUtility::makeInstance(TreeMenu::class);
				$processorConfiguration = [
					'as' => $as,
					'entryPoints' => $data['pages'],
					'depth' => $this->arguments['depth'],
					'excludePages' => $this->arguments['excludePages'],
					'includeNotInMenu' => $this->arguments['includeNotInMenu'] ?? false,
					'excludeDoktypes' => $this->arguments['excludeDoktypes'],
				];
				break;
			default:
				$dataProcessor = GeneralUtility::makeInstance(ListMenu::class);
				$processorConfiguration = [
					'as' => $as,
					'pages' => $data['pages'],
					'includeNotInMenu' => $this->arguments['includeNotInMenu'] ?? false,
					'excludeDoktypes' => $this->arguments['excludeDoktypes'],
				];
		}
		$processorConfiguration = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($processorConfiguration);
		if ($this->arguments['processMedia']) {
			$processorConfiguration['dataProcessing.'] = [
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
		$menu = $dataProcessor->process($contentObjectRenderer, [], $processorConfiguration, [])[$as];
		$data['menu'] = $menu;
		if ($this->arguments['set']) {
			$this->renderingContext->getVariableProvider()->add($this->arguments['set'], $data);
			return null;
		}
		return $data;
	}

	protected function getNavigationElementByIdentifier(string $identifier): ?array
	{
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
			->getQueryBuilderForTable('tt_content');
		$result = $queryBuilder
			->select('*')
			->from('tt_content')
			->where(
				$queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('puck_sys_navigation')),
				$queryBuilder->expr()->eq('subheader', $queryBuilder->createNamedParameter($identifier))
			)
			->executeQuery()
			->fetchAssociative();
		return $result ?: null;
	}
}
<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use UBOS\Puck\Menu\MenuProcessor;

/**
 * Resolves a "puck_sys_navigation" content element by its identifier and returns
 * its configured menu (tree or list) plus the element's label and mode.
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

	/**
	 * @return array<string, mixed>|null Navigation data, or null when stored via "set" or when the element is missing
	 */
	public function render(): ?array
	{
		$navElement = $this->getNavigationElementByIdentifier($this->arguments['identifier']);
		if (!$navElement) {
			return null;
		}

		$data = [
			'identifier' => $this->arguments['identifier'],
			'label' => $navElement['header'],
			'mode' => $navElement['layout'],
			'pages' => $navElement['pages'],
		];
		$data['menu'] = GeneralUtility::makeInstance(MenuProcessor::class)->process(
			$data['mode'],
			[
				'pages' => $data['pages'],
				'depth' => $this->arguments['depth'],
				'excludePages' => $this->arguments['excludePages'],
				'includeNotInMenu' => $this->arguments['includeNotInMenu'],
				'excludeDoktypes' => $this->arguments['excludeDoktypes'],
				'processMedia' => $this->arguments['processMedia'],
			]
		);

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
		$queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
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

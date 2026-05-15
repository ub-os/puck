<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use UBOS\Puck\Attribute\AsAction;

/**
 * Controller for the AnchorMenu plugin.
 * Finds all 'tt_content' records of type 'puck_anchor' on the plugin's page to create a menu.
 */
class AnchorMenuController extends ActionController
{
	use ComponentContentElementTrait;

	#[AsAction("AnchorMenu")]
	public function anchorMenuAction(): ResponseInterface
	{
		$variables = $this->getProcessedData();
		$variables['menu'] = $this->findOnPageAnchorElements($variables['record']->getPid());
		return $this->htmlResponse(
			$this->renderComponent($variables)
		);
	}

	protected function findOnPageAnchorElements(int $pageId): array
	{
		$langId = (int)$this->request->getAttribute('language')->getLanguageId();
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
			->getQueryBuilderForTable('tt_content');
		$queryBuilder->resetRestrictions();
		return $queryBuilder
			->select('*')->from('tt_content')
			->where(
				$queryBuilder->expr()->eq('pid', $pageId),
				$queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('puck_anchor')),
				$queryBuilder->expr()->eq('sys_language_uid', $langId)
			)
			->executeQuery()->fetchAllAssociative();
	}
}

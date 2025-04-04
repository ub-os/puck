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
	use ContentModuleControllerTrait;

	#[AsAction("AnchorMenu")]
	public function anchorMenuAction(): ResponseInterface
	{
		$this->prepareContentView();
		$langId = (int)$this->request->getAttribute('language')->getLanguageId();
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
			->getQueryBuilderForTable('tt_content');
		$menu = $queryBuilder
			->select('*')->from('tt_content')
			->where(
				$queryBuilder->expr()->eq('pid', $this->viewVariables['record']->getPid()),
				$queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('puck_anchor')),
				$queryBuilder->expr()->eq('hidden', 0),
				$queryBuilder->expr()->eq('deleted', 0),
				$queryBuilder->expr()->eq('sys_language_uid', $langId)
			)
			->executeQuery()->fetchAllAssociative();
		$this->viewVariables['menu'] = $menu;
		return $this->htmlResponse(
			$this->renderFluidComponent()
		);
	}

}

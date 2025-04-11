<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use UBOS\MenuControls\Builder\CategoryFilterBuilder;
use UBOS\MenuControls\Builder\PaginationBuilder;
use UBOS\MenuControls\Dto\MenuDemand;

use UBOS\Puck\Attribute\AsAction;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Repository\PageTeaserRepository;
use UBOS\Puck\PageTitle\PuckTitleProvider;

/**
 * Controller for the PageMenu plugin.
 * Creates a menu of pages records based on MenuDemand created from the plugin settings.
 * Will also build CategoryFilter and Pagination objects
 * and override teaser properties of menu items with values from teaser records if configured in settings.
 * @see \UBOS\Puck\Domain\PageRecord::overrideWithTeaser()
 */
class PageMenuController extends ActionController
{
	use ContentModuleControllerTrait;

	protected ?MenuDemand $menuDemand = null;

	protected function getMenuDemand(): MenuDemand
	{
		if (!$this->menuDemand) {
			$demand = $this->settings['demand'];
			$demand['additionalSettings'] = [
				'types' => $this->settings['demand']['types'],
				'navHide' => $this->settings['demand']['navHide'],
				'authors' => $this->settings['demand']['authors'],
				'currentPageId' => $this->request->getAttribute('routing')->getPageId(),
			];
			$this->menuDemand = MenuDemand::createFromArray($demand);
		}
		return $this->menuDemand;
	}

	public function __construct(
		protected PageRepository       $pageRepository,
		protected PageTeaserRepository $pageTeaserRepository,
		protected RecordFactory        $recordFactory
	)
	{
	}

	protected int $pageMenuFragmentTypeNum = 16500000;

	#[AsAction("PageMenu", pluginFragmentPageType: 16500000)]
	public function pageMenuAction(
		?array $demand = null,
		?int   $recordUid = null,
	): ResponseInterface
	{
		// fetch the correct plugin record for contexts where it is not the 'currentContentObject'
		if ($recordUid ?? false) {
			$langId = (int)$this->request->getAttribute('language')->getLanguageId();
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
			$data = $queryBuilder
				->select('*')->from('tt_content')
				->where(
					$queryBuilder->expr()->or(
						$queryBuilder->expr()->eq('uid', $recordUid),
						$queryBuilder->expr()->eq('l18n_parent', $recordUid),
					),
					$queryBuilder->expr()->eq('hidden', 0),
					$queryBuilder->expr()->eq('deleted', 0),
					$queryBuilder->expr()->eq('sys_language_uid', $langId),
				)
				->executeQuery()->fetchAssociative();
			// ensure consistent uid; strict localization mode in non-default languages will return the l18n_parent as uid for the cObj
			$data['uid'] = $recordUid;
			$this->request->getAttribute('currentContentObject')->data = $data;
			$flexForm = GeneralUtility::makeInstance(FlexFormService::class)
				->convertFlexFormContentToArray($data['pi_flexform']);
			$this->settings = array_merge($this->settings, $flexForm['settings']);
		}

		$this->prepareContentView();
		$menu = [];
		$record = $this->viewVariables['record'];

		// apply demand overrides from url parameter
		if ($this->settings['demand']['overrideDemand']) {
			ArrayUtility::mergeRecursiveWithOverrule($this->settings['demand'], $demand ?? []);
		}

		// get raw page records from database
		$pages = $this->pageRepository->findByMenuDemand($this->getMenuDemand(), true);

		// build optional pagination
		$itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?: 12;
		if ($this->settings['pagination']['active'] && count($pages) > $itemsPerPage) {
			$paginationBuilder = new PaginationBuilder(
				records: $pages,
				request: $this->request,
				uriBuilder: $this->uriBuilder,
				menuActionName: 'pageMenu',
				pluginContentRecordUid: $record->getUid(),
				pluginFragmentPageType: $this->pageMenuFragmentTypeNum,
			);
			$pagination = $paginationBuilder
				->configure(array_merge(
					$this->settings['pagination'],
					['pluginContentRecordUidArgumentKey' => 'recordUid']
				))
				->addPaginationLinksToHead()
				->build();
			$this->viewVariables['pagination'] = $pagination;
			$pages = $paginationBuilder->getPaginatedItems();
		}

		// populate menu with resolved page records
		foreach ($pages as $page) {
			$menu[] = $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $page);
		}

		// apply teaser overrides to page records
		if ($this->settings['demand']['teasers']) {
			$teasers = $this->pageTeaserRepository->findByUidList($this->settings['demand']['teasers']);
			foreach ($teasers as $teaser) {
				foreach ($menu as $pageRecord) {
					if ($pageRecord->getUid() === $teaser->page) $pageRecord->overrideWithTeaser($teaser);
				}
			}
		}

		// build optional category filter
		if ($this->settings['categoryFilter']['active']) {
			$categoryFilterBuilder = new CategoryFilterBuilder(
				request: $this->request,
				uriBuilder: $this->uriBuilder,
				menuActionName: 'pageMenu',
				menuRepository: $this->pageRepository,
				menuDemand: $this->getMenuDemand(),
				pluginContentRecordUid: $record->getUid(),
				pluginFragmentPageType: 16500000,
			);
			$categoryFilter = $categoryFilterBuilder
				->configure(array_merge(
					$this->settings['categoryFilter'],
					['pluginContentRecordUidArgumentKey' => 'recordUid']
				))
				->addCategorySuffixToPageTitle(
					GeneralUtility::makeInstance(PuckTitleProvider::class),
					PuckTitleProvider::TITLE_DIVIDER
				)
				->build();
			$this->viewVariables['categoryFilter'] = $categoryFilter;
		}

		$this->viewVariables['menu'] = $menu;
		return $this->htmlResponse(
			$this->renderFluidComponent()
		);
	}
}

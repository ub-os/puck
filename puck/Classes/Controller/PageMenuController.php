<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puck\Attribute\AsPlugin;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puck\Menu\Dto\MenuDemand;
use UBOS\Puck\Menu\CategoryFilterBuilder;
use UBOS\Puck\Menu\PaginationBuilder;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use UBOS\Puck\Domain\Repository\PageTeaserRepository;

#[AsPlugin("PageMenu", fragmentTypeNum: 16500000)]
class PageMenuController extends ActionController
{
	use ContentModuleControllerTrait;

	protected ?MenuDemand $menuDemand = null;

	protected function getMenuDemand(): MenuDemand
	{
		if (!$this->menuDemand) {
			$this->menuDemand = MenuDemand::createFromSettingsArray(
				$this->settings,
				[
					'types' => $this->settings['demand']['types'],
					'navHide' => $this->settings['demand']['navHide'],
					'authors' => $this->settings['demand']['authors'],
					'currentPageId' => $this->request->getAttribute('routing')->getPageId(),
				]
			);
		}
		return $this->menuDemand;
	}

	public function __construct(
		protected CategoryRepository   $categoryRepository,
		protected PageRepository       $pageRepository,
		protected PageTeaserRepository $pageTeaserRepository,
		protected RecordFactory        $recordFactory
	)
	{
	}

	protected int $pageMenuFragmentTypeNum = 16500000;

	public function pageMenuAction(
		?array $demand = null,
		?int   $recordUid = null,
	): ResponseInterface
	{
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

		if ($this->settings['demand']['overrideDemand']) {
			ArrayUtility::mergeRecursiveWithOverrule($this->settings['demand'], $demand ?? []);
		}

		$pages = $this->pageRepository->findByMenuDemand($this->getMenuDemand(), true);
		$itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?: 12;
		if ($this->settings['pagination']['active'] && count($pages) > $itemsPerPage) {
			$paginationBuilder = new PaginationBuilder(
				records: $pages,
				request: $this->request,
				uriBuilder: $this->uriBuilder,
				menuActionName: 'pageMenu',
				contentRecordUid: $record->getUid(),
				fetchLinkPageType: $this->pageMenuFragmentTypeNum,
			);
			$pagination = $paginationBuilder
				->configure($this->settings['pagination'])
				->addPaginationLinksToHead()
				->build();
			$this->viewVariables['pagination'] = $pagination;
			$pages = $paginationBuilder->getPaginatedItems();
		}

		foreach ($pages as $page) {
			$menu[] = $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $page);
		}

		if ($this->settings['demand']['teasers']) {
			$teasers = $this->pageTeaserRepository->findByUidList($this->settings['demand']['teasers']);
			foreach ($teasers as $teaser) {
				foreach ($menu as $pageRecord) {
					if ($pageRecord->getUid() === $teaser->page) $pageRecord->overrideWithTeaser($teaser);
				}
			}
		}

		// add filter categories to view
		if ($this->settings['categoryFilter']['active']) {
			$categoryFilterBuilder = new CategoryFilterBuilder(
				request: $this->request,
				uriBuilder: $this->uriBuilder,
				categoryRepository: $this->categoryRepository,
				menuActionName: 'pageMenu',
				menuRepository: $this->pageRepository,
				menuDemand: $this->getMenuDemand(),
				contentRecordUid: $record->getUid(),
				fetchLinkPageType: 16500000,
			);
			$categoryFilter = $categoryFilterBuilder
				->configure($this->settings['categoryFilter'])
				->addCategorySuffixToPageTitle()
				->build();
			$this->viewVariables['categoryFilter'] = $categoryFilter;
		}

		$this->viewVariables['menu'] = $menu;
		return $this->htmlResponse(
			$this->renderFluidComponent(
				'UBOS\Puck\Modules\PageMenu',
				$this->viewVariables
			)
		);
	}
}

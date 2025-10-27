<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\MenuControls\Builder\CategoryFilterBuilder;
use UBOS\MenuControls\Builder\PaginationBuilder;
use UBOS\MenuControls\Dto\MenuDemand;
use UBOS\Puck\Attribute\AsAction;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Repository\PageTeaserRepository;
use UBOS\Puck\PageTitle\PageTitleProvider;

/**
 * Controller for the PageMenu plugin.
 * Creates a menu of pages records based on MenuDemand created from the plugin settings.
 * Will also build CategoryFilter and Pagination objects
 * and override teaser properties of menu items with values from teaser records if configured in settings.
 * @see \UBOS\Puck\Record\PageRecord::overrideWithTeaser()
 */
class PageMenuController extends ActionController
{
	use ComponentContentElementTrait;

	protected ?MenuDemand $menuDemand = null;

	protected function getMenuDemand(): MenuDemand
	{
		if (!$this->menuDemand) {
			$demand = $this->settings['demand'];
			$demand['additionalSettings'] = [
				'types' => $this->settings['demand']['types'],
				'navHide' => $this->settings['demand']['navHide'],
				'authors' => $this->settings['demand']['authors'] ?? null,
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

	#[AsAction("PageMenu")]
	public function pageMenuAction(
		?array $demand = null,
	): ResponseInterface
	{
		$this->prepareContentView();
		$menu = [];
		$record = $this->viewVariables['record'];

		// apply demand overrides from url parameter
		if ($this->settings['demand']['overrideDemand']) {
			ArrayUtility::mergeRecursiveWithOverrule($this->settings['demand'], $demand ?? []);
		}

		// get raw page records from database
		$pages = $this->pageRepository->findByMenuDemand($this->getMenuDemand(), true);

		// closure to build fragment urls for pagination and category filter
		$fragmentUrlBuilder = fn(array $arguments) => $this->uriBuilder
			->reset()
			->setCreateAbsoluteUri(false)
			->setTargetPageType(1619409324) // typeNum for contentFragmentPage
			->setTargetPageUid($this->request->getAttribute('routing')->getPageId())
			->setArguments(['ceUid' => $record->getUid()])
			->uriFor('pageMenu', $arguments);

		// build optional pagination
		$itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?: 12;
		if ($this->settings['pagination']['active'] && count($pages) > $itemsPerPage) {
			$paginationBuilder = new PaginationBuilder(
				records: $pages,
				request: $this->request,
				uriBuilder: $this->uriBuilder,
				menuActionName: 'pageMenu',
				fragmentUrlBuilder: $fragmentUrlBuilder
			);
			$pagination = $paginationBuilder
				->configure($this->settings['pagination'])
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
		// todo: replace models here with records, update the pageTeaserRepository accordingly
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
				fragmentUrlBuilder: $fragmentUrlBuilder
			);
			$categoryFilter = $categoryFilterBuilder
				->configure($this->settings['categoryFilter'])
				->addCategorySuffixToPageTitle(
					GeneralUtility::makeInstance(PageTitleProvider::class),
					PageTitleProvider::TITLE_DIVIDER
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

<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Menu\Dto\MenuDemand;
use UBOS\Puck\Menu\CategoryFilterBuilder;
use UBOS\Puck\Menu\PaginationBuilder;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use UBOS\Puck\Domain\Repository\ContentRepository;
use UBOS\Puck\Domain\Repository\PageTeaserRepository;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Domain\Model\Content\MenuAnchors;

class MenuController extends ActionController
{
    use ContentControllerTrait;

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
        protected CategoryRepository $categoryRepository,
        protected PageRepository $pageRepository,
        protected ContentRepository $contentRepository,
        protected PageTeaserRepository $pageTeaserRepository,
    )
    {
    }

    protected int $pageMenuFragmentTypeNum = 16500000;
    #[Plugin("PageMenu", fragment: 16500000)]
    public function pageMenuAction(
        ?array $demand = null,
        ?int $recordUid = null): ResponseInterface
    {

        if ($recordUid ?? false) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $data = $queryBuilder
                ->select('*')->from('tt_content')
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($recordUid)))
                ->executeQuery()->fetchAssociative();
            $this->request->getAttribute('currentContentObject')->data = $data;
            $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
            $flexForm = $flexformService->convertFlexFormContentToArray($data['pi_flexform']);
            $flexForm = PuckUtility::convertZeroStringsToInteger($flexForm);
            $this->settings = array_merge($this->settings, $flexForm['settings']);
        }
        $variables = $this->prepareVariables();

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
                contentRecordUid: $variables['record']->getUid(),
                fetchLinkPageType: $this->pageMenuFragmentTypeNum,
            );
            $pagination = $paginationBuilder
                ->configure($this->settings['pagination'])
                ->addPaginationLinksToHead()
                ->build();
            $this->view->assign('pagination', $pagination);
            $variables['menu'] = $this->pageRepository->map($paginationBuilder->getPaginatedItems());
        } else {
            $variables['menu'] = $this->pageRepository->map($pages);
        }

        if ($this->settings['demand']['teasers']) {
            $teasers = $this->pageTeaserRepository->findByUidList($this->settings['demand']['teasers']);
            foreach ($teasers as $key => $teaser) {
                foreach ( $variables['menu'] as $page ) {
                    if ($page->getUid() === $teaser->page) {
                        $page->teaserTitle = $teaser->title;
                        $page->teaserText = $teaser->text;
                        $page->media = $teaser->media;
                    }
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
                contentRecordUid: $variables['record']->getUid(),
                fetchLinkPageType: 16500000,
            );
            $categoryFilter = $categoryFilterBuilder
                ->configure($this->settings['categoryFilter'])
                ->addCategorySuffixToPageTitle()
                ->build();
            $this->view->assign('categoryFilter', $categoryFilter);
        }

        $this->view->assignMultiple($variables);
        $this->view->assign('isFragment', (int)$this->request->getAttribute('routing')->getPageType() === $this->pageMenuFragmentTypeNum);
        $this->view->assign('settings', $this->settings);
        return $this->htmlResponse();
    }

    #[Plugin("AnchorMenu")]
    public function anchorMenuAction(): ResponseInterface
    {
        $langId = $this->request->getAttribute('language')->getLanguageId();
        $variables = $this->prepareVariables();
        $variables['settings'] = $this->settings;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $variables['menu'] = $queryBuilder
            ->select('*')->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($variables['record']->getPid())),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('puck_anchor')),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($langId))
            )
            ->executeQuery()->fetchAllAssociative();
        $this->view->assignMultiple($variables);
        return $this->htmlResponse();
    }

}

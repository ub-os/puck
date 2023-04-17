<?php

/**
 * Page Controller.
 */
declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use HDNET\Autoloader\Annotation\NoCache;
use HDNET\Autoloader\Annotation\Plugin;

use UBOS\Puck\PageTitle\PuckTitleProvider;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puck\Constants;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Domain\Model\Content\MenuPosts;
use UBOS\Puck\Domain\Model\Content\MenuPersons;
use UBOS\Puck\Domain\Repository\Page\PageRepository;


/**
 * Page Controller.
 */
class PageController extends ActionController
{
    /**
     * Render the Page via ExtBase.
     * @Plugin("Page")
     */
    public function indexAction(): string
    {
        try {
            $data = $this->configurationManager->getContentObject()->data;
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            $context = GeneralUtility::makeInstance(Context::class);
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObject = new ContentContentObject($contentObjectRenderer);
            if ($data['doktype'] === Constants::DOKTYPE_POST) {
                $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page\Post', [$data])[0];
            } else if ($data['doktype'] === Constants::DOKTYPE_PERSON) {
                $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page\PersonPage', [$data])[0];
            } else {
                $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page\Page', [$data])[0];
            }

            $variables = [];

            if (array_key_exists('dataProcessing', $this->settings)) {
                $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
                $dataProcessingAsTypoScriptArray = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class)->convertPlainArrayToTypoScriptArray($this->settings['dataProcessing']);
                $variables = $contentDataProcessor->process(
                    $this->configurationManager->getContentObject(),
                    ['dataProcessing.' => $dataProcessingAsTypoScriptArray ?? null],
                    ['data' => $data]
                );
            }

            $backendRows = [
                ['colPos' => 1, 'slide' => 0],
                ['colPos' => 3, 'slide' => -1],
                ['colPos' => 9, 'slide' => 0]
            ];
            foreach($backendRows as $row) {
                $variables['contentElements']['colPos'.$row['colPos']] = $contentObject->render([
                    'table' => 'tt_content',
                    'select.' => [
                        'pidInList' => $data['uid'],
                        'where' => '{#colPos}='.$row['colPos'],
                        'orderBy' => 'sorting',
                    ],
                    'slide' => $row['slide']
                ]);
            }

            $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
            $variables['context'] = [
                'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
                'timestamp'  => $context->getPropertyFromAspect('date', 'timestamp'),
                'site' => $site,
                'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
            ];
            $variables['settings'] = $this->settings;
            $variables['object'] = $model;
            $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
            $this->view->assignMultiple(
                $variables
            );
            return $this->view->render();
        } catch (\Exception $ex) {
            return 'Exception in content rendering: ' . $ex->getMessage();
        }
    }

    protected ?CategoryRepository $categoryRepository = null;
    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }
    protected ?PageRepository $pageRepository = null;
    public function injectPageRepository(PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }

    protected function renderMenu(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
    ): string
    {
        $extensionKey = $this->settings['extensionKey'];
        $vendorName = $this->settings['vendorName'];
        $contentClassName = $this->settings['contentElement'] ?? 'MenuPages';

        $arguments = $this->request->getArguments();

        // get settings from object if provided
        //$settings = $object ? $object->getFlexformArray()['settings'] : $this->settings;
        $this->settings = $this->contentObject ? $this->contentObject->getFlexformArray()['settings'] : $this->settings;

        $this->view->assign('settings', $this->settings);

        // override settings with arguments if overrideDemand is set
        $currentPage = $this->request->hasArgument('page')
            ? (int)$this->request->getArgument('page')
            : 1;
        if ($categoryList && $this->settings['demand']['overrideDemand']) {
            $categoryList && $this->settings['demand']['category']['list'] = $categoryList;
            $categoryConjunction && $this->settings['demand']['category']['conjunction'] = $categoryConjunction;
            $authorList && $this->settings['demand']['author'] = $authorList;
        }
        if (!$this->settings['demand']['category']['conjunction']) {
            $this->settings['demand']['category']['conjunction'] = 'or';
        }

        // get the page objects
        $this->pageRepository->setPageObjectType($this->pageObjectType);
        $pages = $this->pageRepository->findByListSettings($this->settings, $this->allowedDoktypes);

        // map the plugin tt_content data to MenuPages|? content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $this->contentObject = $this->contentObject ?? $dataMapper->map($vendorName.'\\'.$extensionKey.'\\Domain\\Model\\Content\\'.$contentClassName, [$contentObjectData])[0];

        // paginate the records (optional) and add them to the  object
        $itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?: 12;
        if ($this->settings['pagination']['active'] && $pages->count() > $itemsPerPage) {
            $pagination = PuckUtility::paginateQueryResult($pages, $currentPage, $itemsPerPage);
            switch ($this->settings['pagination']['variant']) {
                case 'load-more':
                    $this->view->assign('loadMoreLink', $this->buildMenuLoadMoreLink($pagination['next']));
                    break;
                case 'infinite-scroll':
                    $this->view->assign('infiniteScrollLink', $this->buildMenuLoadMoreLink($pagination['next'], true));
                    break;
                default:
                    $this->view->assign('paginationLinks', $this->buildMenuPaginationLinks($pagination));
            }
            $this->view->assign('pagination', $pagination);
            $this->contentObject->menu = $pagination['items']->toArray();
        } else {
            $this->contentObject->menu = $pages->toArray();
        }

        // add filter categories to view
        if ($this->settings['categoryFilter']['active']) {
            $this->view->assign('categoryFilterLinks', $this->buildMenuCategoryFilterLinks($categoryList));
        }

        // add category to page title
        $this->addMenuCategorySuffixToPageTitle($categoryList);

        $this->view->assign('arguments', [
            'categoryList' => $categoryList,
        ]);
        $this->view->assign('object', $this->contentObject);
        return $this->view->render();
    }

    protected function addMenuCategorySuffixToPageTitle(?string $categoryList): void
    {
        if (!$categoryList) {
            return;
        }
        $query = $this->categoryRepository->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $constraints = [
            $query->in('uid', explode(',',$categoryList)),
        ];
        $categories = $query->matching($query->logicalAnd($constraints))->execute()->toArray();
        $pageTitleSuffixCategory =
            PuckTitleProvider::TITLE_DIVIDER .
            implode(', ', array_map(function(Category $category) {
                return $category->getTitle();
            }, $categories));
        $titleProvider = GeneralUtility::makeInstance(PuckTitleProvider::class);
        $titleProvider->setTitle($titleProvider->getTitle() . $pageTitleSuffixCategory);
    }

    protected function buildMenuPaginationLinks(array $pagination): array
    {
        $links = [];
        if (!$this->contentObject) {
            return $links;
        }
        if ($pagination['prev']) {
            $links['prev'] = $this->buildMenuPaginationLink($pagination['prev']);
            $links['prev']['title'] = '<';
        }
        $links['first'] = $this->buildMenuPaginationLink(1);
        $links['window'] = array_map(function($page) {
            return $this->buildMenuPaginationLink($page);
        }, $pagination['window']);
        $links['last'] = $this->buildMenuPaginationLink($pagination['last']);
        if ($pagination['next']) {
            $links['next'] = $this->buildMenuPaginationLink($pagination['next']);
            $links['next']['title'] = '>';
        }
        return $links;
    }

    protected function buildMenuPaginationLink(int $page): array
    {
        $arguments = $this->request->getArguments();
        $active = $page == intval($arguments['page'] ?? '1');
        unset($arguments['object']);
        if ($page === 1) {
            unset($arguments['page']);
        } else {
            $arguments['page'] = $page;
        }
        return [
            'title' => $page,
            'url' => $this->buildMenuUri($arguments),
            'active' => $active,
            'fetchLinkOptions' => [
                'url' => $this->buildMenuUri($arguments, true),
                'mode' => 'replace',
                'contentId' => 'c' . $this->contentObject->getUid(),
                'scrollToContent' => 1,
            ],
        ];
    }

    protected function buildMenuLoadMoreLink(?int $nextPage, bool $infiniteScroll = false): ?array
    {
        if (!$this->contentObject) {
            return null;
        }
        $link = [
            'fetchLinkOptions' => [
                'contentId' => 'c' . $this->contentObject->getUid() . '-list',
            ]
        ];
        if (!$nextPage) {
            return $link;
        }
        $arguments = $this->request->getArguments();
        unset($arguments['object']);
        $arguments['page'] = $nextPage;
        return [
            'title' => '+',
            'url' => $this->buildMenuUri($arguments),
            'fetchLinkOptions' => [
                'url' => $this->buildMenuUri($arguments, true),
                'mode' => 'append',
                'contentId' => 'c' . $this->contentObject->getUid() . '-list',
                'scrollToContent' => 1,
                'trigger' => $infiniteScroll ? 'scrollIntoView' : 'click',
            ],
        ];
    }

    protected function buildMenuCategoryFilterLinks(?string $categoryList): ?array
    {
        $links = [];
        $groupLinks = [];
        $filterSettings = $this->settings['categoryFilter'];
        if (!$this->contentObject
            || !isset($this->settings['categoryFilter'])
            || !$filterSettings['active']
            || (!$filterSettings['categories'] && !$filterSettings['groupCategories'])
        ) {
            return null;
        }
        $arguments = $this->request->getArguments();
        unset($arguments['page']);
        unset($arguments['object']);
        $query = $this->categoryRepository->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        if ($filterSettings['categories']) {
            $filterCategories = $query
                ->matching($query->in('uid', explode(',',$filterSettings['categories'])))
                ->execute();
            foreach($filterCategories->toArray() as $category) {
                $links[] = $this->buildMenuCategoryFilterLink($categoryList, $category);
            }
        }
        if ($filterSettings['groupCategories']) {
            $groupFilterCategories = $query
                ->matching($query->in('uid', explode(',',$filterSettings['groupCategories'])))
                ->execute();
            foreach($groupFilterCategories->toArray() as $category) {
                $groupLink = [
                    'title' => $category->getTitle(),
                    'links' => [],
                ];
                $subCategories = $query
                    ->matching($query->equals('parent', $category->getUid()))
                    ->execute();
                $activeSubCategories = [];
                foreach($subCategories->toArray() as $subCategory) {
                    if (GeneralUtility::inList($categoryList, (string)$subCategory->getUid())) {
                        $activeSubCategories[] = $subCategory->getUid();
                    }
                }
                foreach($subCategories->toArray() as $subCategory) {
                    $list = $categoryList ? explode(',', $categoryList) : [];
                    if (!$filterSettings['multiSelectWithinGroup'] && $filterSettings['multiSelect'] && $activeSubCategories) {
                        $list = array_diff($list, array_diff($activeSubCategories, [$subCategory->getUid()]));
                    }
                    $groupLink['links'][] = $this->buildMenuCategoryFilterLink(implode(',',$list), $subCategory);
                }
                if ($activeSubCategories) {
                    $closeArguments = $arguments;
                    $closeArguments['categoryList'] = implode(',', array_diff(explode(',', $categoryList), $activeSubCategories)) ?: null;
                    $groupLink['closeLink'] = [
                        'title' => 'x',
                        'url' => $this->buildMenuUri($closeArguments),
                        'fetchLinkOptions' => [
                            'url' => $this->buildMenuUri($closeArguments, true),
                            'mode' => 'replace',
                            'contentId' => 'c' . $this->contentObject->getUid(),
                            'scrollToContent' => 1,
                        ],
                    ];
                }
                $groupLinks[] = $groupLink;
            }
        }

        return array_merge($links, $groupLinks);
    }

    protected function buildMenuCategoryFilterLink($categoryList, $category): array
    {
        $isActive = $categoryList && in_array($category->getUid(), explode(',', $categoryList));
        $newCategoryList = (string)$category->getUid();
        $unsetCategory = false;
        $filterSettings = $this->settings['categoryFilter'];
        $arguments = $this->request->getArguments();
        unset($arguments['page']);
        unset($arguments['object']);
        if ($isActive && !$filterSettings['multiSelect']) {
            $unsetCategory = true;
        }
        if ($filterSettings['multiSelect']) {
            if ($isActive) {
                if ($categoryList == $category->getUid() || !$categoryList) {
                    $unsetCategory = true;
                } else {
                    $newCategoryList = implode(',', array_diff(explode(',', $categoryList), [$category->getUid()]));
                }
            } else {
                $newCategoryList = $categoryList ? $categoryList . ',' . $newCategoryList : $newCategoryList;
            }
        }
        if ($unsetCategory) {
            unset($arguments['categoryList']);
        } else {
            $arguments['categoryList'] = $newCategoryList;
        }
        $link =  [
            'title' => $category->getTitle(),
            'url' => $this->buildMenuUri($arguments),
            'fetchLinkOptions' => [
                'url' => $this->buildMenuUri($arguments, true),
                'mode' => 'replace',
                'contentId' => 'c' . $this->contentObject->getUid(),
                'scrollToContent' => 1,
            ],
            'active' => $isActive,
        ];
        if ($filterSettings['checkPotential']) {
            $checkSettings = $this->settings;
            $checkSettings['demand']['category'] = [
                'list' => $newCategoryList,
                'conjunction' => $this->settings['demand']['category']['conjunction'] ?: 'or',
            ];
            $checkSettings['demand']['limit'] = 1;
            $link['hasNoPotential'] = !$this->pageRepository->findByListSettings($checkSettings, $this->allowedDoktypes)->getFirst();
        }
        return $link;
    }

    protected function buildMenuUri(array $arguments, bool $isFetchUri = false): string
    {
        $builder = GeneralUtility::makeInstance(UriBuilder::class);
        if ($isFetchUri) {
            $arguments['object'] = $this->contentObject;
        }
        $builder
            ->setRequest($this->request)
            ->setCreateAbsoluteUri(!$isFetchUri)
            ->setTargetPageType($isFetchUri ? $this->pluginPageType : 0);
        return $builder->uriFor($this->menuActionName, $arguments, 'Page');
    }

    protected ?array $allowedDoktypes = null;
    protected string $pageObjectType = 'Page';
    protected int $pluginPageType = 16500000;
    protected string $menuActionName = 'menu';
    protected MenuPages|MenuPosts|MenuPersons|MenuJobPosts|null $contentObject = null;


    /**
     * @Plugin("PageMenu")
     */
    public function menuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuPages $object = null): string
    {
        $this->contentObject = $object;
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList);
    }

    /**
     * @Plugin("PostMenu")
     */
    public function postMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuPosts $object = null): string
    {
        $this->allowedDoktypes = [Constants::DOKTYPE_POST];
        $this->pageObjectType = 'Post';
        $this->pluginPageType = 16500001;
        $this->menuActionName = 'postMenu';
        $this->contentObject = $object;
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList);

    }

    /**
     * @Plugin("PersonMenu")
     */
    public function personMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuPersons $object = null): string
    {
        $this->allowedDoktypes = [Constants::DOKTYPE_PERSON];
        $this->pageObjectType = 'PersonPage';
        $this->pluginPageType = 16500002;
        $this->menuActionName = 'personMenu';
        $this->contentObject = $object;
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList);

    }
}

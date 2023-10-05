<?php

/**
 * Page Controller.
 */
declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentDataProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Menu\Trait\Controller\CategoryFilterMenu;
use UBOS\Puck\Menu\Trait\Controller\PaginationMenu;
use UBOS\Puck\Menu\Dto\MenuDemand;

use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Domain\Model\Content\MenuPersons;
use UBOS\Puck\Domain\Model\Content\MenuNews;
use UBOS\Puckloader\Utility\PuckloaderUtility;

/**
 * Page Controller.
 */
class PageController extends ActionController
{
    use CategoryFilterMenu;
    use PaginationMenu;

    protected int $fetchLinkPageType = 16500000;
    protected string $menuActionName = 'menu';
    protected ?MenuDemand $menuDemand = null;
    protected MenuPages|MenuNews|MenuPersons|null $menuContentObject = null;

    protected function getCategoryRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }
    protected function getMenuRepository(): PageRepository
    {
        return $this->pageRepository;
    }
    protected function getMenuActionName(): string
    {
        return $this->menuActionName;
    }
    protected function getMenuContentObjectUid(): int
    {
        return $this->menuContentObject->getUid();
    }
    protected function getFetchLinkPageType(): int
    {
        return $this->fetchLinkPageType;
    }
    protected function getMenuDemand(): MenuDemand
    {
        if (!$this->menuDemand) {
            $this->menuDemand = MenuDemand::createFromSettingsArray(
                $this->settings,
                [
                    'navHide' => $this->settings['demand']['navHide'],
                    'author' => $this->settings['demand']['author'],
                    'currentPageId' => $this->request->getAttribute('routing')->getPageId()
                ]
            );
        }
        return $this->menuDemand;
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

    #[Plugin("Page")]
    public function indexAction(): ResponseInterface
    {
        $data = $this->configurationManager->getContentObject()->data;
        $context = GeneralUtility::makeInstance(Context::class);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject = new ContentContentObject($contentObjectRenderer);
        $contentObject->setContentObjectRenderer($contentObjectRenderer);
        $contentObject->setRequest($this->request);
        $model = $this->pageRepository->findByUid($data['uid']);

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

        // to do update, replace with alternative
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
        $frontendUserAspect = $context->getAspect('frontend.user');
        $variables['context'] = [
            'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
            'timestamp'  => $context->getPropertyFromAspect('date', 'timestamp'),
            'site' => $site,
            'frontendUser' => [
                'username' => $frontendUserAspect->get('username'),
                'isLoggedIn' => $frontendUserAspect->get('isLoggedIn'),
                'isAdmin' => $frontendUserAspect->get('isAdmin'),
                'groupIds' => $frontendUserAspect->get('groupIds'),
                'groupNames' => $frontendUserAspect->get('groupNames'),
            ],
            'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
        ];

        $variables['settings'] = $this->settings;
        $variables['object'] = $model;
        $this->view->setTemplateRootPaths([$this->settings['view']['templateRootPath']]);
        $this->view->assignMultiple(
            $variables
        );
        return $this->htmlResponse();

    }

    protected function renderMenu(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
    ): ResponseInterface
    {
        $extensionKey = $this->settings['extensionKey'];
        $vendorName = $this->settings['vendorName'];
        $contentClassName = $this->settings['contentElement'] ?? 'MenuPages';

        // get settings from object if provided
        $this->settings = $this->menuContentObject ? $this->menuContentObject->getFlexForms()['piFlexform']['settings'] : $this->settings;

        $this->view->assign('settings', $this->settings);

        if ($categoryList && $this->settings['demand']['overrideDemand']) {
            $categoryList && $this->settings['demand']['category']['list'] = $categoryList;
            $categoryConjunction && $this->settings['demand']['category']['conjunction'] = $categoryConjunction;
            $authorList && $this->settings['demand']['author'] = $authorList;
        }
        if (!$this->settings['demand']['category']['conjunction']) {
            $this->settings['demand']['category']['conjunction'] = 'or';
        }

        $records = $this->getMenuRepository()->findByMenuDemand($this->getMenuDemand());

        // map the plugin tt_content data to MenuPages|? content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $this->menuContentObject = $this->menuContentObject ?? $dataMapper->map($vendorName.'\\'.$extensionKey.'\\Domain\\Model\\Content\\'.$contentClassName, [$contentObjectData])[0];

        // paginate the records (optional) and add them to the  object
        $itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?: 12;
        if ($this->settings['pagination']['active'] && $records->count() > $itemsPerPage) {
            $paginator = $this->createPaginator($records, $itemsPerPage);
            $numberedPaginator = $this->createNumberedPaginator($paginator);
            $this->view->assign('pagination', $this->buildPagination(
                $paginator,
                $numberedPaginator,
                $this->settings['pagination']['variant']
            ));
            $this->menuContentObject->menu = $paginator->getPaginatedItems()->toArray();
        } else {
            $this->menuContentObject->menu = $records->toArray();
        }

        // add filter categories to view
        if ($this->settings['categoryFilter']['active']) {
            $this->view->assign('categoryFilter', $this->buildCategoryFilter(
                categoryList: $categoryList ?: '',
                filterCategories: $this->settings['categoryFilter']['categories'] ?: null,
                groupCategories: $this->settings['categoryFilter']['groupCategories'] ?: null,
                groupDepth: intval($this->settings['categoryFilter']['groupDepth'] ?? 1),
                order: ['title' => QueryInterface::ORDER_ASCENDING]
            ));
        }

        // add category to page title
        $this->addMenuCategorySuffixToPageTitle($categoryList);

        $this->view->assign('object', $this->menuContentObject);
        return $this->htmlResponse();
    }

    #[Plugin("PageMenu")]
    public function menuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuPages $object = null): ResponseInterface
    {
        $this->fetchLinkPageType = 16500000;
        $this->menuActionName = 'menu';
        $this->menuContentObject = $object;
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList);
    }

    #[Plugin("NewsMenu")]
    public function newsMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuNews $object = null): ResponseInterface
    {
        $this->getMenuRepository()->setAllowedTypes([
            PageRepository::DOKTYPES['news'],
            PageRepository::DOKTYPES['link'],
            PageRepository::DOKTYPES['shortcut'],
        ]);
        $this->fetchLinkPageType = 16500001;
        $this->menuActionName = 'newsMenu';
        $this->menuContentObject = $object;
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList);
    }

    #[Plugin("PersonMenu")]
    public function personMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuPersons $object = null): ResponseInterface
    {
        $this->getMenuRepository()->setAllowedTypes([PageRepository::DOKTYPES['person']]);
        $this->fetchLinkPageType = 16500002;
        $this->menuActionName = 'personMenu';
        $this->menuContentObject = $object;
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList);
    }
}

<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Menu\Trait\Controller\CategoryFilterMenu;
use UBOS\Puck\Menu\Trait\Controller\PaginationMenu;
use UBOS\Puck\Menu\Dto\MenuDemand;

use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Repository\ContentRepository;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Domain\Model\Content\MenuPersons;
use UBOS\Puck\Domain\Model\Content\MenuNews;

class MenuController extends ActionController
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
    protected ?ContentRepository $contentRepository = null;
    public function injectContentRepository(ContentRepository $contentRepository) : void
    {
        $this->contentRepository = $contentRepository;
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
        $this->getMenuRepository()->setAllowedTypes(PageRepository::DEFAULT_ALLOWED_TYPES);
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

    #[Plugin("AnchorMenu")]
    public function anchorMenuAction(): ResponseInterface
    {
        $data = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $variables['object'] = $dataMapper->map($this->settings['modelNamespace'] . $this->settings['modelName'], [$data])[0];
        $variables['object']->setAnchors($this->contentRepository->findContentObjectsBy('Anchor', 'pid', $data['pid'])->toArray());
        $variables['settings'] = $this->settings;
        $this->view->assignMultiple(
            $variables
        );
        return $this->htmlResponse();
    }
}

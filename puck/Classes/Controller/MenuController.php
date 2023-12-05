<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Puck\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Menu\Dto\MenuDemand;
use UBOS\Puck\Menu\CategoryFilterBuilder;
use UBOS\Puck\Menu\PaginationBuilder;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\Domain\Repository\ContentRepository;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Domain\Model\Content\MenuPersons;
use UBOS\Puck\Domain\Model\Content\MenuNews;

class MenuController extends ActionController
{
    protected ?MenuDemand $menuDemand = null;
    protected function getMenuDemand(): MenuDemand
    {
        if (!$this->menuDemand) {
            $this->menuDemand = MenuDemand::createFromSettingsArray(
                $this->settings,
                [
                    'navHide' => $this->settings['demand']['navHide'],
                    'author' => $this->settings['demand']['author'],
                    'currentPageId' => $this->request->getAttribute('routing')->getPageId(),
                    'allowedTypes' => $this->settings['types']
                ]
            );
        }
        return $this->menuDemand;
    }

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected PageRepository $pageRepository,
        protected ContentRepository $contentRepository,
    )
    {
    }

    #[Plugin("PageMenu")]
    public function pageMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = null,
        ?string $authorList = null,
        ?MenuPages $object = null): ResponseInterface
    {
        if ($object) {
            // get settings from object if provided
            $this->settings = $object->getFlexForms()['piFlexform']['settings'];
        } else {
            // map the plugin tt_content data to MenuPages content object
            $contentObjectData =$this->request->getAttribute('currentContentObject')->data;
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            $contentClassName = $this->settings['contentElement'] ?? 'MenuPages';
            $object = $dataMapper->map('UBOS\\Puck\\Domain\\Model\\Content\\'.$contentClassName, [$contentObjectData])[0];
        }

        // prepare settings for demand
        if ($categoryList && $this->settings['demand']['overrideDemand']) {
            $categoryList && $this->settings['demand']['category']['list'] = $categoryList;
            $categoryConjunction && $this->settings['demand']['category']['conjunction'] = $categoryConjunction;
            $authorList && $this->settings['demand']['author'] = $authorList;
        }
        if (!$this->settings['demand']['category']['conjunction']) {
            $this->settings['demand']['category']['conjunction'] = 'or';
        }

        $records = $this->pageRepository->findByMenuDemand($this->getMenuDemand());

        // paginate the records (optional) and add them to the  object
        $itemsPerPage = (int)$this->settings['pagination']['itemsPerPage'] ?: 12;
        if ($this->settings['pagination']['active'] && $records->count() > $itemsPerPage) {
            $paginationBuilder = new PaginationBuilder(
                result: $records,
                request: $this->request,
                uriBuilder: $this->uriBuilder,
                menuActionName: 'pageMenu',
                menuContentObjectUid: $object->getUid(),
                fetchLinkPageType: 16500000,
            );
            $pagination = $paginationBuilder
                ->configure($this->settings['pagination'])
                ->addPaginationLinksToHead()
                ->build();
            $this->view->assign('pagination', $pagination);
            $object->menu = $paginationBuilder->getPaginatedItems()->toArray();
        } else {
            $object->menu = $records->toArray();
        }

        // add filter categories to view
        if ($this->settings['categoryFilter']['active']) {
            $categoryFilterBuilder = new CategoryFilterBuilder(
                request: $this->request,
                uriBuilder: $this->uriBuilder,
                categoryRepository: $this->categoryRepository,
                menuRepository: $this->pageRepository,
                menuDemand: $this->getMenuDemand(),
                menuActionName: 'pageMenu',
                menuContentObjectUid: $object->getUid(),
                fetchLinkPageType: 16500000,
            );
            $categoryFilter = $categoryFilterBuilder
                ->configure($this->settings['categoryFilter'])
                ->addCategorySuffixToPageTitle()
                ->build();
            $this->view->assign('categoryFilter', $categoryFilter);

        }
        $this->view->assign('object', $object);
        $this->view->assign('settings', $this->settings);
        return $this->htmlResponse();
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

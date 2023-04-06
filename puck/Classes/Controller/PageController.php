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
            $contentDataProcessor = GeneralUtility::makeInstance(ContentDataProcessor::class);
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObject = new ContentContentObject($contentObjectRenderer);
            if ($data['doktype'] === Constants::DOKTYPE_POST) {
                $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page\Post', [$data])[0];
            } else if ($data['doktype'] === Constants::DOKTYPE_PERSON) {
                $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page\PersonPage', [$data])[0];
            } else {
                $model = $dataMapper->map('UBOS\Puck\Domain\Model\Page\Page', [$data])[0];
            }
            $backendRows = [
                ['colPos' => 1, 'slide' => 0],
                ['colPos' => 3, 'slide' => -1],
                ['colPos' => 9, 'slide' => 0]
            ];
            $contentElements = [];
            foreach($backendRows as $row) {
                $contentElements['colPos'.$row['colPos']] = $contentObject->render([
                    'table' => 'tt_content',
                    'select.' => [
                        'pidInList' => $data['uid'],
                        'where' => '{#colPos}='.$row['colPos'],
                        'orderBy' => 'sorting',
                    ],
                    'slide' => $row['slide']
                ]);
            }
            $variables = $contentDataProcessor->process(
                $this->configurationManager->getContentObject(),
                ['dataProcessing.' => $this->settings['dataProcessing'] ?? null],
                ['data' => $data]
            );
            $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
            $variables['context'] = [
                'backendUser' => $context->getPropertyFromAspect('backend.user', 'username'),
                'timestamp'  => $context->getPropertyFromAspect('date', 'timestamp'),
                'site' => $site,
                'language' => $site->getLanguageById($context->getPropertyFromAspect('language', 'id')),
            ];
            $variables['settings'] = $this->settings;
            $variables['object'] = $model;
            $variables['contentElements'] = $contentElements;
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

    protected function renderMenu(
        ?string $categoryList = null,
        ?string $categoryConjunction = 'or',
        ?string $authorList = null,
        MenuPages|MenuPosts|MenuPersons|null $object = null,
        ?array $allowedDoktypes = null,
        ?string $className = 'Page'): string
    {
        $extensionKey = $this->settings['extensionKey'];
        $vendorName = $this->settings['vendorName'];
        $name = $this->settings['contentElement'] ?? 'MenuPages';

        // get settings from object if provided
        $settings = $object ? $object->getFlexformArray()['settings'] : $this->settings;
        $this->view->assign('settings', $settings);

        $currentPage = $this->request->hasArgument('page')
            ? (int)$this->request->getArgument('page')
            : 1;
        if ($categoryList && $settings['demand']['overrideDemand']) {
            $settings['demand']['category'] = [
                'list' => $categoryList,
                'conjunction' => $categoryConjunction
            ];
        }
        if ($authorList && $settings['demand']['overrideDemand']) {
            $settings['demand']['author'] = $authorList;
        }

        // get the pages
        $this->pageRepository->setPageObjectType($className);
        $pages = $this->pageRepository->findByListSettings($settings, $allowedDoktypes);

        // map the plugin tt_content data to MenuPages content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $object ?? $dataMapper->map($vendorName.'\\'.$extensionKey.'\\Domain\\Model\\Content\\'.$name, [$contentObjectData])[0];

        //$object->layout = $settings['template']['layout'];
        // paginate the records (optional) and add them to the  object
        $itemsPerPage = $settings['pagination']['itemsPerPage'] ? (int)$settings['pagination']['itemsPerPage'] : 12;
        if ($settings['pagination']['active'] && $pages->count() > $itemsPerPage) {
            $pagination = PuckUtility::paginateQueryResult($pages, $currentPage, $itemsPerPage);
            $object->menu = $pagination['items']->toArray();
            $this->view->assign('pagination', $pagination);
        } else {
            $object->menu = $pages->toArray();
        }

        // add filter categories to view
        if ($settings['template']['categoryFilter'] && $settings['template']['filterCategories']){
            $query = $this->categoryRepository->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            $constraints = [
                $query->in('uid', explode(',',$settings['template']['filterCategories'])),
            ];
            $filterCategories = $query->matching($query->logicalAnd($constraints))->execute();
            $this->view->assign('filter', [
                'categories' => $filterCategories,
                'current' => $categoryList,
            ]);
        }

        // add category to page title
        $pageTitleSuffixCategory = '';
        if ($categoryList) {
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
        }
        $titleProvider = GeneralUtility::makeInstance(PuckTitleProvider::class);
        $titleProvider->setTitle($titleProvider->getTitle() . $pageTitleSuffixCategory);

        $this->view->assign('arguments', [
            'categoryList' => $categoryList,
        ]);
        $this->view->assign('object', $object);
        return $this->view->render();
    }


    /**
     * @param string|null $categoryList
     * @param string|null $categoryConjunction
     * @param string|null $authorList
     * @param MenuPages|null $object
     * @return string
     * @Plugin("PageMenu")
     */
    public function menuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = 'or',
        ?string $authorList = null,
        ?MenuPages $object = null): string
    {
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList, $object);
    }

    /**
     * @param string|null $categoryList
     * @param string|null $categoryConjunction
     * @param string|null $authorList
     * @param MenuPosts|null $object
     * @return string
     * @Plugin("PostMenu")
     */
    public function postMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = 'or',
        ?string $authorList = null,
        ?MenuPosts $object = null): string
    {
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList, $object, [Constants::DOKTYPE_POST], 'Post');
    }

    /**
     * @param string|null $categoryList
     * @param string|null $categoryConjunction
     * @param string|null $authorList
     * @param MenuPersons|null $object
     * @return string
     * @Plugin("PersonMenu")
     */
    public function personMenuAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = 'or',
        ?string $authorList = null,
        ?MenuPersons $object = null): string
    {
        return $this->renderMenu($categoryList, $categoryConjunction, $authorList, $object, [Constants::DOKTYPE_PERSON], 'PersonPage');
    }
}

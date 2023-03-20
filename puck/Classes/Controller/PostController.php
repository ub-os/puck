<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use UBOS\Puck\Domain\Repository\Page\PostRepository;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Utility\PuckUtility;

/**
 * Post Controller.
 */
class PostController extends ActionController
{
    protected ?PostRepository $postRepository = null;
    public function injectPostRepository(PostRepository $postRepository): void
    {
        $this->postRepository = $postRepository;
    }

    protected ?CategoryRepository $categoryRepository = null;
    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function listAction(
        ?string $categoryList = null,
        ?string $categoryConjunction = 'or',
        ?string $authorList = null,
        ?array $settings = null,
        ?MenuPages $object = null): string
    {
        $settings = $settings ?? $this->settings;
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
        $posts = $this->postRepository->findByListSettings($settings);

        // map the plugin tt_content data to MenuPages content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $object ?? $dataMapper->map('UBOS\Puck\Domain\Model\Content\MenuPages', [$contentObjectData])[0];
        $object->layout = $settings['template']['layout'];

        // paginate the posts (optional) and add them to the MenuPages object
        $itemsPerPage = $settings['pagination']['itemsPerPage'] ? (int)$settings['pagination']['itemsPerPage'] : 12;
        if ($settings['pagination']['active'] && $posts->count() > $itemsPerPage) {
            $pagination = PuckUtility::paginateQueryResult($posts, $currentPage, $itemsPerPage);
            $object->setMenu($pagination['items']->toArray());
            $this->view->assign('pagination', $pagination);
        } else {
            $object->setMenu($posts->toArray());
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
        $this->view->assign('arguments', [
            'categoryList' => $categoryList,
        ]);
        $this->view->assign('object', $object);
        return $this->view->render();
    }
}

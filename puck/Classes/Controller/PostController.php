<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use UBOS\Puck\Domain\Repository\PostRepository;
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

    public function listAction(?string $categoryList = null, ?string $categoryConjunction = 'or'): string
    {
        $settings = $this->settings;
        $currentPage = $this->request->hasArgument('page')
            ? (int)$this->request->getArgument('page')
            : 1;
        if ($categoryList && $settings['constraints']['overrideDemand']) {
            $settings['constraints']['category'] = [
                'list' => $categoryList,
                'conjunction' => $categoryConjunction
            ];
        }
        $posts = $this->postRepository->findByListSettings($settings);

        // map the plugin tt_content data to MenuPages content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $dataMapper->map('UBOS\Puck\Domain\Model\Content\MenuPages', [$contentObjectData])[0];
        $object->layout = $settings['template']['layout'];

        // paginate the posts (optional) and add them to the MenuPages object
        $itemsPerPage = $settings['pagination']['itemsPerPage'] ? (int)$settings['pagination']['itemsPerPage'] : 12;
        if ($settings['pagination']['active'] && ((int)$settings['constraints']['limit'] > $itemsPerPage) || !(int)$settings['constraints']['limit']) {
            $pagination = PuckUtility::paginateQueryResult($posts, $currentPage, $itemsPerPage);
            $object->setMenu($pagination['items']->toArray());
            $this->view->assign('pagination', $pagination);
        } else {
            $object->setMenu($posts->toArray());
        }

        $this->view->assign('arguments', [
            'categoryList' => $categoryList,
        ]);
        $this->view->assign('object', $object);
        return $this->view->render();
    }


}

<?php

/**
 * Page Controller.
 */
declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

use UBOS\Puck\Domain\Repository\PostRepository;
use UBOS\Puck\Utility\PuckUtility;


/**
 * Page Controller.
 */
class PostController extends ActionController
{
    protected PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
    public function indexAction(): string
    {
        return $this->view->render();
    }

    public function listAction(): string
    {
        $currentPage = $this->request->hasArgument('page')
            ? (int)$this->request->getArgument('page')
            : 1;
        $posts = $this->postRepository->findAll();
        $pagination = PuckUtility::paginateData($posts, $currentPage, 1);

        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $dataMapper->map('UBOS\Puck\Domain\Model\Content\MenuPages', [$contentObjectData])[0];
        $object->setMenu($pagination['items']->toArray());
        $object->setMenuItemConfig('media,title,date,categories,teaserText');
        $object->layout = 'posts-default';
        $object->itemColumnWidth = 4;

        $this->view->assign('pagination', $pagination);
        $this->view->assign('object', $object);
        return $this->view->render();
    }
}

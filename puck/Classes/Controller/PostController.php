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
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
        $posts = $this->postRepository->findByListSettings($this->settings);

        // map the plugin tt_content data to MenuPages content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $dataMapper->map('UBOS\Puck\Domain\Model\Content\MenuPages', [$contentObjectData])[0];
        $object->layout = $this->settings['template']['layout'];

        // paginate the posts (optional) and add them to the MenuPages object
        $itemsPerPage = $this->settings['pagination']['itemsPerPage'] ?? 12;
        if ($this->settings['pagination']['active'] && (int)$this->settings['constraints']['limit'] > (int)$itemsPerPage) {
            $pagination = PuckUtility::paginateQueryResult($posts, $currentPage, $this->settings['pagination']['itemsPerPage']);
            $object->setMenu($pagination['items']->toArray());
            $this->view->assign('pagination', $pagination);
        } else {
            $object->setMenu($posts->toArray());
        }

        $this->view->assign('object', $object);
        return $this->view->render();
    }


}

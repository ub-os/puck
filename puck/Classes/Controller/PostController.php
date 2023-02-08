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

    public function listAction(?string $authorList = null, ?string $categoryList = null, ?string $categoryConjunction = 'or'): string
    {
        $settings = $this->settings;
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
        $object = $dataMapper->map('UBOS\Puck\Domain\Model\Content\MenuPages', [$contentObjectData])[0];
        $object->layout = $settings['template']['layout'];

        // paginate the posts (optional) and add them to the MenuPages object
        $itemsPerPage = $settings['pagination']['itemsPerPage'] ? (int)$settings['pagination']['itemsPerPage'] : 12;
        if ($settings['pagination']['active'] && ((int)$settings['demand']['limit'] > $itemsPerPage) || !(int)$settings['demand']['limit']) {
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

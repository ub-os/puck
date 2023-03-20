<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use UBOS\Puck\Domain\Repository\PersonRepository;
use UBOS\Puck\Domain\Model\Content\MenuPages;
use UBOS\Puck\Utility\PuckUtility;

/**
 * Person Controller.
 */
class PersonController extends ActionController
{
    protected ?PersonRepository $personRepository = null;
    public function injectPersonRepository(PersonRepository $personRepository): void
    {
        $this->personRepository = $personRepository;
    }

    public function listAction(
        ?array $settings = null,
        ?MenuPages $object = null): string
    {
        $settings = $settings ?? $this->settings;
        $this->view->assign('settings', $settings);
        $currentPage = $this->request->hasArgument('page')
            ? (int)$this->request->getArgument('page')
            : 1;
        $persons = $this->personRepository->findByListSettings($settings);

        // map the plugin tt_content data to MenuPages content object
        $contentObjectData = $this->configurationManager->getContentObject()->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $object ?? $dataMapper->map('UBOS\Puck\Domain\Model\Content\MenuPages', [$contentObjectData])[0];
        $object->layout = $settings['template']['layout'];

        // paginate the posts (optional) and add them to the MenuPages object
        $itemsPerPage = $settings['pagination']['itemsPerPage'] ? (int)$settings['pagination']['itemsPerPage'] : 12;
        if ($settings['pagination']['active'] && $persons->count() > $itemsPerPage) {
            $pagination = PuckUtility::paginateQueryResult($persons, $currentPage, $itemsPerPage);
            $object->setMenu($pagination['items']->toArray());
            $this->view->assign('pagination', $pagination);
        } else {
            $object->setMenu($persons->toArray());
        }
        $this->view->assign('object', $object);
        return $this->view->render();
    }
}

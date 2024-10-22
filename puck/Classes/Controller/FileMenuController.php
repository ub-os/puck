<?php

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Resource\FileCollectionRepository;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Domain\Model\Content\MenuFiles;

class FileMenuController extends ActionController
{
    use ContentControllerTrait;
    public function __construct(
        protected FileCollectionRepository $fileCollectionRepository,
    )
    {
    }

    #[Plugin("FileMenu")]
    public function fileMenuAction(): ResponseInterface
    {
        $variables = $this->prepareVariables();
        $variables['menu'] = [];
        foreach($variables['record']->get('media') as $file) {
            $variables['menu'][] = $file;
        }
        foreach($variables['record']->get('file_collections') as $fileCollection) {
            foreach($fileCollection->get('files') as $file) {
                $variables['menu'][] = $file;
            }
        }
        $variables['menu'] = $this->sortMenu(
            $variables['menu'],
            $variables['record']->get('filelink_sorting'),
            $variables['record']->get('filelink_sorting_direction')
        );
        $variables['settings'] = $this->settings;
        $this->view->assignMultiple($variables);
        return $this->htmlResponse();
    }

    public function getFilesFromCollectionUid($uid): ?array
    {
        $collection = $this->fileCollectionRepository->findByUid($uid);
        $collection->loadContents();
        return $collection->getItems();
    }

    protected function sortMenu(array $menu, string $filelinkSorting, string $filelinkSortingDirection): array
    {
        if ($filelinkSorting) {
            usort($menu, function ($a, $b) use ($filelinkSorting, $filelinkSortingDirection)
            {
                $valA = $a->getProperties()[$filelinkSorting];
                $valB = $b->getProperties()[$filelinkSorting];
                if ($filelinkSortingDirection === 'desc') {
                    if (is_string($valA)) {
                        return strcasecmp($valA, $valB);
                    }
                    return $valA < $valB;
                } else {
                    if (is_string($valA)) {
                        return strcasecmp($valB, $valA);
                    }
                    return $valA > $valB;
                }
            }
            );
        }
        return $menu;
    }
}
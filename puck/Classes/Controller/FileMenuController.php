<?php

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Resource\FileCollectionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use UBOS\Puckloader\Attribute\Plugin;

use UBOS\Puck\Domain\Model\Content\MenuFiles;

class FileMenuController extends ActionController
{
    public function __construct(
        protected FileCollectionRepository $fileCollectionRepository,
    )
    {
    }

    #[Plugin("FileMenu")]
    public function fileMenuAction(): ResponseInterface
    {
        $contentObjectData = $this->request->getAttribute('currentContentObject')->data;
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $object = $dataMapper->map(MenuFiles::class, [$contentObjectData])[0];
        $menu = [];
        foreach($object->assets as $fileReference) {
            $menu[] = $fileReference->getOriginalResource();
        }
        foreach(explode(',', $object->fileCollections) as $uid) {
            $menu = array_merge($menu, $this->getFilesFromCollectionUid($uid));
        }
        $object->menu = $this->sortMenu($menu, $object->filelinkSorting, $object->filelinkSortingDirection);
        $variables['settings'] = $this->settings;
        $variables['object'] = $object;
        $this->view->assignMultiple(
            $variables
        );
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
            usort($menu, function ($a, $b)
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
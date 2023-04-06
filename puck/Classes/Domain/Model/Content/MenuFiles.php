<?php

namespace UBOS\Puck\Domain\Model\Content;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;

use UBOS\Puck\Domain\Model\FileCollection;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("03_menu")
 */
class MenuFiles extends Text
{

    /**
     * @var ?ObjectStorage<FileReference>
     * @Lazy
     */
    public ?ObjectStorage $assets = null;

    /**
     * @var ?ObjectStorage<FileCollection>
     * @Lazy
     */
    public ?ObjectStorage $fileCollections = null;

    /**
     * @var string
     */
    public string $filelinkSorting = '';
    /**
     * @var string
     */
    public string $filelinkSortingDirection = '';

    /**
     * @var string
     */
    public string $target = '';

    /**
     * @var string
     */
    public string $itemColumnWidth = '';

    protected ?array $menu = null;

    public function getMenu(): ?array
    {
        if ($this->menu === null) {
            $menu = [];
            foreach($this->assets as $fileReference) {
                $menu[] = $fileReference->getOriginalResource()->getOriginalFile();
            }
            foreach($this->fileCollections as $fileCollection) {
                $menu = array_merge($menu, $fileCollection->getFiles());
            }
            $this->menu = $this->sortMenu($menu);
        }
        return $this->menu;
    }

    protected function sortMenu(array $menu): array
    {
        if ($this->filelinkSorting) {
            usort($menu, function ($a, $b)
            {
                $valA = $a->getProperties()[$this->filelinkSorting];
                $valB = $b->getProperties()[$this->filelinkSorting];
                if ($this->filelinkSortingDirection === 'desc') {
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
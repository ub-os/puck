<?php

namespace UBOS\Puck\Domain\Model\Content;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ContentElementWizard;
use UBOS\Puckloader\Attribute\ModelPersistence;
use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puck\Domain\Model\FileCollection;
use UBOS\Puckloader\Attribute\PluginElement;

#[ModelPersistence("tt_content")]
#[ContentElementWizard('03_menu')]
#[PluginElement('FileMenu')]
class MenuFiles extends Text
{

    /**
     * @var ObjectStorage<FileReference>|null
     * @Lazy
     */
    public ObjectStorage|null $assets = null;

    public ?string $fileCollections = null;

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

    public ?array $menu = null;

}
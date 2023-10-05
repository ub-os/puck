<?php
namespace UBOS\Puck\Domain\Model\Content\Trait;

use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use UBOS\Puckloader\Attribute\ModelColumn;

trait TextMediaLayout {

    /**
     * @var ObjectStorage<FileReference>|null
     * @Lazy
     */
    #[ModelColumn("string")]
    public ObjectStorage|null $assets = null;

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $mediaLayout = '';

    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $textColumnWidth = 0;

    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $mediaColumnWidth = 0;

    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $itemColumnWidth = 0;

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $rowJustify = '';

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $rowAlign = '';

    /**
     * @var int
     */
    #[ModelColumn("int")]
    public int $mediaMaxHeight = 0;

    /**
     * @return string
     */
    public function getMediaLayoutDirection(): string
    {
        if (in_array($this->mediaLayout, ['above','below'])) {
            return 'column';
        }
        return 'row';
    }

    /**
     * @var string
     */
    #[ModelColumn("string")]
    public string $cardMediaSize = '';
}
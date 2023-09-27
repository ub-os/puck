<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
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
<?php
namespace UBOS\Puck\Domain\Model\Trait\Content;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use HDNET\Autoloader\Annotation\DatabaseField;

trait TextMediaLayout {

    /**
     * @var ?ObjectStorage<FileReference>
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     * @Lazy
     */
    public ?ObjectStorage $assets = null;

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $mediaLayout;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $textColumnWidth = 0;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $mediaColumnWidth = 0;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $itemColumnWidth = 0;

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $rowJustify = '';

    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $rowAlign = '';

    /**
     * @var int
     * @DatabaseField("int")
     */
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
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $cardMediaSize = '';
}
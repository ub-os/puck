<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\DatabaseField;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;

/**
 * @DatabaseTable("tx_puck_domain_model_inline_media")
 */
class InlineMedia extends AbstractEntity
{
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $parentUid;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $parentTable;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $header = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $subheader = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $icon = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $bodytext = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $mediaLayout;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $layout = '';
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $columnWidth = 0;
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $itemColumnWidth = 0;
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $mediaColumnWidth = 0;
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $textColumnWidth = 0;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $rowJustify = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $rowAlign = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $cardMediaSize = '';
    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $mediaMaxHeight = 0;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $itemType = '';
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $frameClass = '';
    /**
     * @var ?ObjectStorage<FileReference>
     * @DatabaseField("string")
     * @Lazy
     */
    public ?ObjectStorage $assets = null;
    /**
     * @var string
     * @Transient
     */
    protected string $mediaLayoutDirection = '';
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
}
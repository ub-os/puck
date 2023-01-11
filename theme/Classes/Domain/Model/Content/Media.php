<?php

namespace UBOS\Theme\Domain\Model\Content;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use HDNET\Autoloader\Annotation\WizardTab;
use UBOS\Theme\Domain\Model\Page;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;

/**
 * @DatabaseTable("tt_content")
 * @WizardTab("01_content")
 */
class Media extends Text
{
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $mediaLayout;

    /**
     * @var ?ObjectStorage<FileReference>
     * @Lazy
     */
    public ?ObjectStorage $assets = null;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $bodytext2;

    /**
     * @var ObjectStorage<Page>
     * @Lazy
     */
    public ObjectStorage $pages;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $contentType;

    /**
     * @var int
     * @DatabaseField("int")
     */
    public int $itemColumnWidth = 6;

    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $columnPosition = '';

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
    public int $mediaMaxHeight = 0;

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
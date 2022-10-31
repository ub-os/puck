<?php

namespace UBOS\Theme\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;


/**
 * @DatabaseTable("pages")
 */
class Page extends AbstractEntity
{

    /**
     * @var string
     */
    public string $title;

    /**
     * @var string
     */
    public string $slug;

    /**
     * @var string
     */
    public string $navTitle;

    /**
     * @var string
     */
    public string $subtitle;

    /**
     * @var string
     */
    public string $seoTitle;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var string
     */
    public string $abstract;

    /**
     * @var string
     */
    public string $keywords;

    /**
     * @var string
     */
    public string $author;

    /**
     * @var string
     */
    public string $authorEmail;

    /**
     * @var string
     */
    public string $lastUpdated;

    /**
     * @var string
     */
    public string $layout;

    /**
     * @var string
     */
    public string $backendLayout;

    /**
     * @var ObjectStorage<FileReference>
     */
    public ObjectStorage $media;

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }
}
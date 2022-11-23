<?php

namespace UBOS\Theme\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference ;
use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;

/**
 * @DatabaseTable("pages")
 */
class Page extends AbstractEntity
{

    /**
     *
     */
    public function __construct() {
        $this->media = new ObjectStorage();
        $this->subpages = new ObjectStorage();
    }

    /**
     * @var string
     */
    public string $title = '';

    /**
     * @var string
     */
    public string $slug;

    /**
     * @var string
     */
    public string $navTitle = '';

    /**
     * @var string
     */
    public string $subtitle = '';

    /**
     * @var string
     */
    public string $seoTitle = '';

    /**
     * @var string
     */
    public string $description = '';

    /**
     * @var string
     */
    public string $abstract = '';

    /**
     * @var string
     */
    public string $keywords = '';

    /**
     * @var string
     */
    public string $author = '';

    /**
     * @var string
     */
    public string $authorEmail = '';

    /**
     * @var string
     */
    public string $lastUpdated = '';

    /**
     * @var string
     */
    public string $layout = '';

    /**
     * @var string
     */
    public string $backendLayout = '';

    /**
     * @var ObjectStorage<FileReference>
     * @Lazy
     */
    public $media = null;

    /**
     * @var array
     * @Transient
     * @Lazy
     */
    public array $breadcrumbs = [];

    /**
     * @var array
     * @Transient
     * @Lazy
     */
    public array $subpages = [];

    /**
     * @var FileReference|null
     * @Transient
     * @Lazy
     */
    public $primaryImage = null;

    /**
     * @return string
     */
    public function getNavTitle(): string
    {
        return $this->navTitle ? : $this->title;
    }

    /**
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->seoTitle ? : $this->title;
    }

    /**
     * @return string
     */
    public function setPrimaryImage($rootLineIndex = 0)
    {
        $rootline = array_reverse($this->breadcrumbs);
         if ($rootline[$rootLineIndex]->media->count() > 0) {
            $this->primaryImage = $rootline[$rootLineIndex]->media->current();
        } else if ($rootLineIndex < (count($rootline) - 1)) {
             $this->setPrimaryImage($rootLineIndex + 1);
        }
    }

    /**
     * @param array $breadcrumbs
     * @return void
     */
    public function setBreadcrumbs(array $breadcrumbs): void
    {
        $this->breadcrumbs = $breadcrumbs;
    }

}
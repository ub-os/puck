<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Object\ObjectManager;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;

use UBOS\Puck\Domain\Repository\PageRepository;

/**
 * @DatabaseTable("pages")
 */
class Page extends AbstractEntity
{
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
    public string $subtitle = '';
    /**
     * @var string
     */
    public string $description = '';
    /**
     * @var string
     * @DatabaseField(type="string")
     */
    public string $teaserText = '';
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
     * @var int
     */
    public int $navHide = 0;
    /**
     * @var ?ObjectStorage<FileReference>
     * @Lazy
     */
    public ?ObjectStorage $media = null;
    /**
     * @var string
     * @DatabaseField("string")
     */
    public string $icon = '';
    /**
     * @var string
     */
    protected string $navTitle = '';
    /**
     * @var string
     */
    protected string $seoTitle = '';
    /**
     * @var ?array
     * @Transient
     * @Lazy
     */
    protected ?array $childPages = null;
    /**
     * @var ?array
     * @Transient
     * @Lazy
     */
    protected ?array $breadcrumbs = null;
    /**
     * @var ?FileReference
     * @Transient
     * @Lazy
     */
    protected ?FileReference $primaryImage = null;

    /**
     *
     */
    public function __construct() {
        $this->media = new ObjectStorage();
    }

    /**
     * @return ?array
     */
    public function getChildPages(): ?array
    {
        if ($this->childPages === null) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $pageRepository = $objectManager->get(PageRepository::class);
            $this->childPages = $pageRepository->findPagesByPid($this->getUid());
        }
        return $this->childPages;
    }

    /**
     * @param ?array $childPages
     */
    public function setChildPages(?array $childPages): void
    {
        $this->childPages = $childPages;
    }

    /**
     * @return ?FileReference
     */
    public function getPrimaryImage(): ?FileReference
    {
        if ($this->primaryImage === null) {
            $this->primaryImage = $this->getPrimaryImageFromRootLine();
        }
        return $this->primaryImage;
    }

    /**
     * @param ?FileReference $primaryImage
     */
    public function setPrimaryImage(?FileReference $primaryImage): void
    {
        $this->primaryImage = $primaryImage;
    }

    /**
     * @param int $rootLineIndex
     * @return ?FileReference
     */
    protected function getPrimaryImageFromRootLine(int $rootLineIndex = 0): ?FileReference
    {
        $rootLine = array_reverse($this->getBreadcrumbs());
        if ($rootLine[$rootLineIndex]->media->count() > 0) {
            return $rootLine[$rootLineIndex]->media->current();
        } else if ($rootLineIndex < (count($rootLine) - 1)) {
            return $this->getPrimaryImageFromRootLine($rootLineIndex + 1);
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getBreadcrumbs(): array
    {
        if ($this->breadcrumbs === null) {
            $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $this->getUid());
            $this->breadcrumbs =($dataMapper->map('UBOS\Puck\Domain\Model\Page', $rootLine->get()));
        }
        return $this->breadcrumbs;
    }

    /**
     * @param ?array $breadcrumbs
     */
    public function setBreadcrumbs(?array $breadcrumbs): void
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @return string
     */
    public function getNavTitle(): string
    {
        return $this->navTitle ? : $this->title;
    }

    /**
     * @param string $navTitle
     */
    public function setNavTitle(string $navTitle): void
    {
        $this->navTitle = $navTitle;
    }

    /**
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->seoTitle ? : $this->title;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle(string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

}
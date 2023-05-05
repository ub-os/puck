<?php

namespace UBOS\Puck\Domain\Model\Page;

use HDNET\Autoloader\Annotation\DatabaseField;
use HDNET\Autoloader\Annotation\DatabaseTable;
use HDNET\Autoloader\Annotation\EnableRichText;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Frontend\Page\PageLayoutResolver;

/**
 * @DatabaseTable("pages")
 */
class Page extends AbstractEntity
{
    public int $doktype = 0;
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
     * @DatabaseField("string", sql="varchar(1024) DEFAULT '' NOT NULL")
     */
    public string $teaserText = '';
    /**
     * @var string
     * @DatabaseField("string", sql="varchar(255) DEFAULT '' NOT NULL")
     */
    public string $icon = '';
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
     * @var int
     */
    public int $navHide = 0;
    /**
     * @var ObjectStorage<Category>|null
     * @Lazy
     */
    public ObjectStorage|null $categories = null;
    /**
     * @var ObjectStorage<FileReference>|null
     * @Lazy
     */
    public ObjectStorage|null $media = null;
    /**
     * @var string
     */
    protected string $backendLayout = '';
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
     */
    protected ?array $rootLine = null;
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

    public function getBackendLayout(): string
    {
        if ($this->backendLayout === '') {
            $pageLayoutResolver = GeneralUtility::makeInstance(PageLayoutResolver::class);
            $this->backendLayout =
                str_replace(
                    'pagets__',
                    '',
                    $pageLayoutResolver->getLayoutForPage(['backend_layout' => ''], $this->getRootline()));
        }
        return $this->backendLayout;
    }

    public function getRootLine(): array
    {
        if ($this->rootLine === null) {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $this->getUid());
            $this->rootLine = $rootLine->get();
        }
        return $this->rootLine;
    }

    /**
     * @param int $rootLineIndex
     * @param string $column
     * @return array
     */
    protected function getColumnFromRootLine(string $column, int $rootLineIndex = 0): array
    {
        $currentPage = $this->getRootline()[$rootLineIndex];
        if ($currentPage[$column]) {
            $column = $currentPage[$column];
        } else if ($rootLineIndex < (count($this->getRootline()) - 1)) {
            return $this->getColumnFromRootLine($column, $rootLineIndex + 1);
        } else {
            $column = null;
        }
        return ['rootLineIndex' => $rootLineIndex, 'column' => $column];
    }

    public function setBackendLayout(string $backendLayout): void
    {
        $this->backendLayout = $backendLayout;
    }

    /**
     * @param string $navTitle
     */
    public function setNavTitle(string $navTitle): void
    {
        $this->navTitle = $navTitle;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle(string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param array|null $rootLine
     */
    public function setRootLine(?array $rootLine): void
    {
        $this->rootLine = $rootLine;
    }

}
<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Frontend\Page\PageLayoutResolver;
use UBOS\Puck\Attribute\Persistence;

/**
 * Default model for 'pages'
 */
#[Persistence("pages")]
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
	 */
	protected string $navTitle = '';

	public function getNavTitle(): string
	{
		return $this->navTitle ?: $this->title;
	}

	/**
	 * @var string
	 */
	public string $breadcrumbTitle = '';

	public function getBreadcrumbTitle(): string
	{
		return $this->breadcrumbTitle ?: $this->getNavTitle();
	}

	/**
	 * @var string
	 */
	public string $teaserTitle = '';

	public function getTeaserTitle(): string
	{
		return $this->teaserTitle ?: $this->title;
	}

	/**
	 * @var string
	 */
	public string $teaserText = '';

	/**
	 * @var string
	 */
	public string $teasers = '';

	/**
	 * @var string
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
	#[Persistence("lastUpdated")]
	public string $lastUpdated = '';
	/**
	 * @var string
	 */
	public string $layout = '';
	/**
	 * @var string
	 */
	public string $target = '';
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

	public string $txSchemaWebpagetype = '';

	/**
	 * @var string
	 */
	protected string $backendLayout = '';
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
	public function getSeoTitle(): string
	{
		return $this->seoTitle ?: $this->title;
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

	public function getLinkParameter(): string
	{
		return $this->getUid();
	}

}
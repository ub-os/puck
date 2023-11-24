<?php

declare(strict_types=1);

namespace UBOS\Puck\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\DebugUtility;

final class PuckTitleProvider extends AbstractPageTitleProvider
{
    public const TITLE_DIVIDER = ' | ';

    public function __construct(
        private readonly SiteFinder $siteFinder
    ) {
    }

    public function getTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }
        $page = $GLOBALS['TSFE']->page;
        if ($page['seo_title']) {
            return $page['seo_title'];
        }
        $siteTitle = $GLOBALS['TSFE']->getLanguage()->getWebsiteTitle() ?: $GLOBALS['TSFE']->getSite()->getAttribute('websiteTitle');
        return $page['title'] . self::TITLE_DIVIDER . $siteTitle;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}
<?php

declare(strict_types=1);

namespace UBOS\Puck\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\DebugUtility;

final class PuckTitleProvider extends AbstractPageTitleProvider
{
    public const TITLE_DIVIDER = ' | ';

    public function getTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }
        $pageRecord = $this->request->getAttribute('frontend.page.information')->getPageRecord();
        if ($pageRecord['seo_title']) {
            return $pageRecord['seo_title'];
        }
        $site = $this->request->getAttribute('site');
        $language = $this->request->getAttribute('language');
        $siteTitle = $language->getWebsiteTitle() ?: $site->getAttribute('websiteTitle');
        return $pageRecord['title'] . self::TITLE_DIVIDER . $siteTitle;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}
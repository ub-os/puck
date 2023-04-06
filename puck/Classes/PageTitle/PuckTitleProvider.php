<?php

declare(strict_types=1);

namespace UBOS\Puck\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\Utility\DebugUtility;

use UBOS\Puck\Constants;

final class PuckTitleProvider extends AbstractPageTitleProvider
{
    public const SITE_TITLE = Constants::SITE_TITLE;

    public const TITLE_DIVIDER = ' | ';

    public function __construct(
    ) {
        $this->title = $this->getTitle();
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
        return $page['title'] . self::TITLE_DIVIDER . self::SITE_TITLE;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}
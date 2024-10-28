<?php

namespace UBOS\Puck\Domain;

use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Puck\Domain\Model\PageTeaser;

class PageRecord extends Record
{
    public function __construct(
        protected readonly RawRecord         $rawRecord,
        protected array                      $properties,
        protected readonly ?SystemProperties $systemProperties = null,
    )
    {
        $this->setComputedProperties();
    }

    protected function setComputedProperties(): void
    {
        $p = $this->properties;
        if ($this->has('nav_title')) {
            $p['nav_title'] = $p['nav_title'] ?: $p['title'];
        }
        if ($this->has('seo_title')) {
            $p['seo_title'] = $p['seo_title'] ?: $p['title'];
        }
        if ($this->has('teaser_title')) {
            $p['teaser_title'] = $p['teaser_title'] ?: $p['title'];
        }
        if ($this->has('breadcrumb_title')) {
            $p['breadcrumb_title'] = $p['breadcrumb_title'] ?: $p['nav_title'];
        }
        if ($this->has('target') && !$p['target']) {
            $p['target'] = '_self';
        }

        $p['link_parameter'] = $this->has('url') && $p['url'] ? $p['url'] : $this->rawRecord->getUid();
        $this->properties = $p;
    }

    public function overrideWithTeaser(PageTeaser $teaser): void
    {
        $this->properties['teaser_title'] = $teaser->title;
        $this->properties['teaser_text'] = $teaser->text;
        $this->properties['media'] = $teaser->media;
    }

}

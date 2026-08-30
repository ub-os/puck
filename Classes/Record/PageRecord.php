<?php

namespace UBOS\Puck\Record;

use TYPO3\CMS\Core\Domain\Page as CorePageRecord;
use UBOS\Puck\Domain\Model\PageTeaser;

/**
 * Record for 'pages'
 */
class PageRecord extends CorePageRecord
{
	/**
	 * initialize computed properties based on the current properties
	 */
	public function setComputedProperties(): void
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

		$p['link_parameter'] = $this->getUid();
		if ($this->has('url') && $this->getRawRecord()->get('url')) {
			$p['link_parameter'] = $this->get('url');
		}
		$this->properties = $p;
	}

	/**
	 * override teaser properties with values from the given teaser model
	 * used in @see \UBOS\Puck\Controller\PageMenuController
	 */
	public function overrideWithTeaser(PageTeaser $teaser): void
	{
		$this->properties['teaser_title'] = $teaser->title;
		$this->properties['teaser_text'] = $teaser->text;
		// Normalise the Extbase file references to core FileReference objects
		$media = [];
		foreach ($teaser->media ?? [] as $reference) {
			$media[] = $reference->getOriginalResource();
		}
		$this->properties['media'] = $media;
	}

}

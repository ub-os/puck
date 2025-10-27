<?php

namespace UBOS\Puck\Record;

use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\SystemProperties;

/**
 * Record for 'tt_content'
 */
class ContentRecord extends Record
{
	public function __construct(
		protected readonly RawRecord         $rawRecord,
		protected array                      $properties,
		protected readonly ?SystemProperties $systemProperties = null,
	)
	{
		$this->setOverriddenProperties();
		$this->setComputedProperties();
	}

	/**
	 * override property values based on TCA 'valueOverrides'
	 * @see \UBOS\Puck\Configuration\ContentElementConfiguration
	 */
	protected function setOverriddenProperties(): void
	{
		foreach ($GLOBALS['TCA']['tt_content']['types'][$this->properties['CType']]['valueOverrides'] ?? [] as $fieldName => $value) {
			$this->properties[$fieldName] = $value;
		}
	}

	/**
	 * initialize computed properties based on the current properties
	 */
	protected function setComputedProperties(): void
	{
		$p = $this->properties;
		if ($this->has('header') && $this->has('header_spacing_override')) {
			$p['remove_header_spacing'] = (!$p['header'] || $p['header_layout'] > 29) && !$p['header_spacing_override'];
		}

		if ($this->has('media_layout')) {
			$p['media_layout_direction'] = in_array($p['media_layout'], ['above', 'below']) ? 'column' : 'row';
		}

		if ($p['CType'] === 'puck_cover_media') {
			if (in_array($p['media_layout'], ['left', 'right'])) {
				$p['container_width'] = 12;
				$p['container_offset'] = 0;
			}
		}

		if ($this->has('menu_item_config')) {
			$settings = $p['menu_item_config'];
			$p['menu_item_config'] = [];
			foreach ($settings as $key) {
				$p['menu_item_config'][$key] = true;
			}
		}
		$this->properties = $p;
	}
}

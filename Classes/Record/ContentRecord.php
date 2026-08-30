<?php

namespace UBOS\Puck\Record;

use TYPO3\CMS\Core\Domain\Record;

/**
 * Record for 'tt_content'
 */
class ContentRecord extends Record
{
	/**
	 * override property values based on TCA 'valueOverrides'
	 * @see \UBOS\Puck\Configuration\ContentElementConfiguration
	 */
	public function setOverriddenProperties(): void
	{
		foreach ($GLOBALS['TCA']['tt_content']['types'][$this->properties['CType']]['valueOverrides'] ?? [] as $fieldName => $value) {
			$this->properties[$fieldName] = $value;
		}
	}

	/**
	 * initialize computed properties based on the current properties
	 */
	public function setComputedProperties(): void
	{
		$prop = $this->properties;

		if ($this->has('header_layout') && is_string($prop['header_layout'])) {
			$headerConfig = array_map('trim', explode('.', $prop['header_layout']));
			$prop['header_layout'] = [
				'tag' => $headerConfig[0] ?? 'h2',
				'class' => $headerConfig[1] ?? '',
				'subheaderClass' => $headerConfig[2] ?? '',
				'value' => $prop['header_layout'],
			];
			// if the tag is numeric (e.g. from older versions of the content element), default to 'h2'
			if (is_numeric($prop['header_layout']['tag'])) {
				$prop['header_layout']['tag'] = 'h2';
			}
		}

		if ($this->has('header') && $this->has('header_layout') && $this->has('header_spacing_override')) {
			$prop['_remove_header_spacing'] = (!$prop['header'] || $prop['header_layout']['tag'] !== 'h2') && !$prop['header_spacing_override'];
		}

		if ($this->has('media_layout')) {
			$prop['_media_layout_direction'] = in_array($prop['media_layout'], ['above', 'below']) ? 'column' : 'row';
			if ($prop['CType'] === 'puck_cover_media') {
				if (in_array($prop['media_layout'], ['left', 'right'])) {
					$prop['container_width'] = 12;
					$prop['container_offset'] = 0;
				}
			}
		}

		if ($this->has('menu_item_config')) {
			$settings = $prop['menu_item_config'];
			$prop['menu_item_config'] = [];
			foreach ($settings as $key) {
				$prop['menu_item_config'][$key] = true;
			}
		}
		$this->properties = $prop;
	}
}

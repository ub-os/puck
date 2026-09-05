<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility as ExtUtil;
use UBOS\Puck\Configuration\ContentElementConfiguration;

foreach (glob(ExtUtil::extPath('puck', 'Configuration/TCA/Overrides/tt_content/*.php')) ?: [] as $file) {
	include $file;
}
foreach (ContentElementConfiguration::getOrderedConfigurationsFromFolder('Configuration/ContentElements/*.php') as $conf) {
	$conf->addTCA();
}

/**
 * !!!
 * Removes most of the default EXT:frontend CType items, because we replace them with our versions
 * kinda hacky, filters out all CType items with 'group' in default,menu,lists
 */
foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $index => $item) {
	if (in_array($item['group'], ['default','menu','lists'])) {
		unset($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][$index]);
	}
}


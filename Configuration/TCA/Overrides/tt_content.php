<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility as ExtUtil;
use UBOS\Puck\Configuration\ContentElementConfiguration;

foreach (glob(ExtUtil::extPath('puck', 'Configuration/TCA/Overrides/tt_content/*.php')) ?: [] as $file) {
	include $file;
}
foreach (ContentElementConfiguration::getOrderedConfigurationsFromFolder('Configuration/ContentElements/*.php') as $conf) {
	$conf->addTCA();
}

//$GLOBALS['TCA']['tt_content']['types']['powermail_pi1']['previewRenderer'] = \In2code\Powermail\Hook\PluginPreviewRenderer::class;

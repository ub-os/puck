<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

foreach (glob(ExtensionManagementUtility::extPath('puck') . 'Configuration/TCA/Overrides/tt_content/*.php') as $file) {
    include $file;
}
foreach (glob(ExtensionManagementUtility::extPath('puck') . 'Configuration/ContentElements/*.php') as $file) {
    (include $file)?->addTCA();
}

//$GLOBALS['TCA']['tt_content']['types']['powermail_pi1']['previewRenderer'] = \In2code\Powermail\Hook\PluginPreviewRenderer::class;

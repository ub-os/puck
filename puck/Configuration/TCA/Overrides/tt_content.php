<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Preview\PuckPreviewRenderer;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puckloader\Loader;

foreach (glob(ExtensionManagementUtility::extPath('puck') . 'Configuration/TCA/Content/*.php') as $file) {
    require $file;
}

foreach (glob(ExtensionManagementUtility::extPath('puck') . 'Configuration/ContentElements/*.php') as $element) {
    (include $element)->addTCA();
}

Loader::loadTca('puck');
$contentTcaPath = ExtensionManagementUtility::extPath('puck', 'Configuration/TCA/Content');
$typeNames = PuckUtility::getBaseFilesInDir($contentTcaPath . '/Types/', 'php');
foreach ($typeNames as $type) {
    require $contentTcaPath . '/Types/' . $type . '.php';
    if (str_starts_with($type, 'puck_')) {
        $GLOBALS['TCA']['tt_content']['types'][$type]['previewRenderer'] = PuckPreviewRenderer::class;
    }
}
$GLOBALS['TCA']['tt_content']['types']['powermail_pi1']['previewRenderer'] = In2code\Powermail\Hook\PluginPreviewRenderer::class;

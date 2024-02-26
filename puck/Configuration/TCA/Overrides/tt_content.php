<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Preview\PuckPreviewRenderer;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puckloader\Loader;

$contentTcaPath = ExtensionManagementUtility::extPath('puck', 'Configuration/TCA/Content');

$columns = require $contentTcaPath . '/tt_content__columns.php';
$palettes = require $contentTcaPath . '/tt_content__palettes.php';

Loader::loadTca('puck');

$typeNames = PuckUtility::getBaseFilesInDir($contentTcaPath . '/Types/', 'php');
foreach ($typeNames as $type) {
    require $contentTcaPath . '/Types/' . $type . '.php';
    if (str_starts_with($type, 'puck_')) {
        $GLOBALS['TCA']['tt_content']['types'][$type]['previewRenderer'] = PuckPreviewRenderer::class;
    }}


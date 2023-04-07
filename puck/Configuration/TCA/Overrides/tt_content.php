<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use UBOS\Puck\Loader\SmartContainerContentObjectLoader;
use UBOS\Puck\Loader\SmartContentObjectLoader;
use UBOS\Puck\Preview\PuckPreviewRenderer;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Puck\Utility\PuckUtility;

SmartContentObjectLoader::registerTypes();

$contentTcaPath = ExtensionManagementUtility::extPath('puck', 'Configuration/TCA/Content');
$typeNames = PuckUtility::getBaseFilesInDir($contentTcaPath . '/Types/', 'php');
foreach ($typeNames as $type) {
    $newTca = require($contentTcaPath . '/Types/' . $type . '.php');
    $tca = $GLOBALS['TCA']['tt_content']['types'][$type] ?? [];
    ArrayUtility::mergeRecursiveWithOverrule(
        $tca,
        $newTca,
        true, true, false
    );
    $GLOBALS['TCA']['tt_content']['types'][$type] = $tca;
}



$columns = require __DIR__.'/../Content/columns.php';
$palettes = require __DIR__.'/../Content/palettes.php';

foreach($columns as $name => $column) {
    $GLOBALS['TCA']['tt_content']['columns'][$name] = $column;
}
foreach($palettes as $name => $palette) {
    $GLOBALS['TCA']['tt_content']['palettes'][$name] = $palette;
}

$GLOBALS['TCA']['tt_content']['ctrl']['previewRenderer'] = PuckPreviewRenderer::class;

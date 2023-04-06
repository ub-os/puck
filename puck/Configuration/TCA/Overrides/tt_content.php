<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use UBOS\Puck\Loader\SmartContainerContentObjectLoader;
use UBOS\Puck\Loader\SmartContentObjectLoader;
use UBOS\Puck\Preview\PuckPreviewRenderer;

SmartContentObjectLoader::registerTypes();
SmartContainerContentObjectLoader::registerTypes();

$columns = require __DIR__.'/../Content/columns.php';
$palettes = require __DIR__.'/../Content/palettes.php';
$types = require __DIR__.'/../Content/types.php';

foreach($columns as $name => $column) {
    $GLOBALS['TCA']['tt_content']['columns'][$name] = $column;
}
foreach($palettes as $name => $palette) {
    $GLOBALS['TCA']['tt_content']['palettes'][$name] = $palette;
}
foreach($types as $name => $type) {
    $orgType = $GLOBALS['TCA']['tt_content']['types'][$name] ?? [];
    ArrayUtility::mergeRecursiveWithOverrule(
        $orgType,
        $type,
        true, true, false
    );
    $GLOBALS['TCA']['tt_content']['types'][$name] = $orgType;
}

$GLOBALS['TCA']['tt_content']['ctrl']['previewRenderer'] = PuckPreviewRenderer::class;

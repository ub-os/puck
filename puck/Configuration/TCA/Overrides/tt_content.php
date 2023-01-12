<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
    $GLOBALS['TCA']['tt_content']['types'][$name] = $type;
}
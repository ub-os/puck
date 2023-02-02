<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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

ExtensionManagementUtility::addTcaSelectItemGroup(
    'tt_content',
    'list_type',
    'post',
    'Blog',
    'after:default'
);

ExtensionUtility::registerPlugin(
    'puck',
    'PostList',
    'Post menu',
    'menu_posts',
    'post'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['puck_postlist'] = 'pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['puck_postlist'] = 'pi_flexform';

ExtensionManagementUtility::addPiFlexFormValue(
    'puck_postlist',
    'FILE:EXT:puck/Configuration/FlexForms/PostList.xml'
);
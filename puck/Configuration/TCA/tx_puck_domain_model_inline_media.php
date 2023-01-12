<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ctrl = [
    'label' => 'header',
    'label_alt' => 'subheader,bodytext',
    'title' => 'Inline items',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'sortby' => 'sorting',
    'versioningWS' => true,
    'languageField' => 'sys_language_uid',
    'transOrigPointerField' => 'l10n_parent',
    'transOrigDiffSourceField' => 'l10n_diffsource',
    'delete' => 'deleted',
    //'hideTable' => true,
    //'iconfile' => 'EXT:puck/Resources/Public/Icons/Content/InlineMedia.svg',
    'enablecolumns' => [
        'disabled' => 'hidden',
    ],
    'searchFields' => 'header',
    'type' => 'item_type',
    'typeicon_column' => 'item_type',
    'typeicon_classes' => [
        'default' => 'inline_media',
        'accordions' => 'accordions_inline_media',
        'columns' => 'columns_inline_media',
        'cards' => 'cards_inline_media',
        'hero_carousel' => 'hero_carousel_inline_media',
    ],
];
$interface = [
    'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, header, subheader, bodytext',
];


return [
    'ctrl' => $ctrl,
    'interface' => $interface,
    'columns' => require 'InlineMedia/columns.php',
    'palettes' => require 'InlineMedia/palettes.php',
    'types' => require 'InlineMedia/types.php',
];

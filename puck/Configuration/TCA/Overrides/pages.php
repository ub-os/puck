<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Domain\Repository\PageRepository;

require __DIR__.'/../Pages/columns.php';
require __DIR__.'/../Pages/palettes.php';

$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-news'] = 'news_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-presets'] = 'preset_folder';

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['pages'],
    [
        // add all page standard fields and tabs to your new page type
        'types' => [
            PageRepository::DOKTYPES['news'] => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPES['default']]['showitem'],
                'columnsOverrides' => [
                    'post_date' => [
                        'config' => [
                            'required' => 1,
                        ]
                    ]
                ]
            ],
            PageRepository::DOKTYPES['person'] => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPES['default']]['showitem'],
            ]
        ]
    ]
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;;teaser',
    '',
    'after:--palette--;;title'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;;meta',
    '',
    'after:--palette--;;title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'url, --palette--;;postMeta',
    PageRepository::DOKTYPES['news'],
    'replace:--palette--;;meta'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'page_persons',
    PageRepository::DOKTYPES['person'],
    'before:--palette--;;title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'php_tree_stop',
    254,
    'after:module'
);
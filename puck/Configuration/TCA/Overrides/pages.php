<?php

use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use UBOS\Puck\Constants;

require __DIR__.'/../Pages/columns.php';
require __DIR__.'/../Pages/palettes.php';


$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-posts'] = 'post_folder';

// types
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Detail plugin page',
        Constants::DOKTYPE_DETAIL_PLUGIN,
        'detail_plugin_page'
    ],
    '1',
    'after'
);
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Person page',
        Constants::DOKTYPE_PERSON,
        'person_page'
    ],
    '1',
    'after'
);
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Blog post',
        Constants::DOKTYPE_POST,
        'blog_post'
    ],
    '1',
    'after'
);
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Overview page',
        Constants::DOKTYPE_OVERVIEW,
        'overview_page'
    ],
    '1',
    'after'
);
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Start page',
        Constants::DOKTYPE_START,
        'start_page'
    ],
    '1',
    'after'
);

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['pages'],
    [
        // add icon for new page type:
        'ctrl' => [
            'typeicon_classes' => [
                Constants::DOKTYPE_START => 'start_page',
                Constants::DOKTYPE_START . '-hideinmenu' => "start_page_hideinmenu",
                Constants::DOKTYPE_START . '-root' => "apps-pagetree-page-domain",

                Constants::DOKTYPE_OVERVIEW => 'overview_page',
                Constants::DOKTYPE_OVERVIEW . '-hideinmenu' => "overview_page_hideinmenu",
                Constants::DOKTYPE_OVERVIEW . '-root' => "apps-pagetree-page-domain",

                Constants::DOKTYPE_POST => 'blog_post',
                Constants::DOKTYPE_POST . '-hideinmenu' => "blog_post_hideinmenu",
                Constants::DOKTYPE_POST . '-root' => "apps-pagetree-page-domain",

                Constants::DOKTYPE_PERSON => 'person_page',
                Constants::DOKTYPE_PERSON . '-hideinmenu' => "person_page_hideinmenu",
                Constants::DOKTYPE_PERSON . '-root' => "apps-pagetree-page-domain",

            ],
        ],
        // add all page standard fields and tabs to your new page type
        'types' => [
            Constants::DOKTYPE_POST => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPE_DEFAULT]['showitem'],
                'columnsOverrides' => [
                    'post_date' => [
                        'config' => [
                            'required' => 1,
                        ]
                    ]
                ]
            ],
            Constants::DOKTYPE_PERSON => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPE_DEFAULT]['showitem'],
            ]
        ]
    ]
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;;postTitle',
    Constants::DOKTYPE_POST,
    'replace:--palette--;;title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'page_persons',
    Constants::DOKTYPE_PERSON,
    'before:--palette--;;title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'php_tree_stop',
    254,
    'after:module'
);
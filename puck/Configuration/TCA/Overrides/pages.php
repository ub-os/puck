<?php

use UBOS\Puck\Domain\Repository\Page\PageRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

require __DIR__.'/../Pages/columns.php';
require __DIR__.'/../Pages/palettes.php';


$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-posts'] = 'post_folder';

// types
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Detail plugin page',
        PageRepository::DOKTYPE_DETAIL_PLUGIN,
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
        PageRepository::DOKTYPE_PERSON,
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
        PageRepository::DOKTYPE_POST,
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
        PageRepository::DOKTYPE_OVERVIEW,
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
        PageRepository::DOKTYPE_START,
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
                (string)PageRepository::DOKTYPE_START => 'start_page',
                PageRepository::DOKTYPE_START . '-hideinmenu' => "start_page_hideinmenu",
                PageRepository::DOKTYPE_START . '-root' => "apps-pagetree-page-domain",

                (string)PageRepository::DOKTYPE_OVERVIEW => 'overview_page',
                PageRepository::DOKTYPE_OVERVIEW . '-hideinmenu' => "overview_page_hideinmenu",
                PageRepository::DOKTYPE_OVERVIEW . '-root' => "apps-pagetree-page-domain",

                (string)PageRepository::DOKTYPE_POST => 'blog_post',
                PageRepository::DOKTYPE_POST . '-hideinmenu' => "blog_post_hideinmenu",
                PageRepository::DOKTYPE_POST . '-root' => "apps-pagetree-page-domain",

                (string)PageRepository::DOKTYPE_PERSON => 'person_page',
                PageRepository::DOKTYPE_PERSON . '-hideinmenu' => "person_page_hideinmenu",
                PageRepository::DOKTYPE_PERSON . '-root' => "apps-pagetree-page-domain",

                (string)PageRepository::DOKTYPE_DETAIL_PLUGIN => 'detail_plugin_page',
                PageRepository::DOKTYPE_DETAIL_PLUGIN . '-hideinmenu' => "detail_plugin_page_hideinmenu",
                PageRepository::DOKTYPE_DETAIL_PLUGIN . '-root' => "apps-pagetree-page-domain",
            ],
        ],
        // add all page standard fields and tabs to your new page type
        'types' => [
            PageRepository::DOKTYPE_POST => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT]['showitem'],
                'columnsOverrides' => [
                    'post_date' => [
                        'config' => [
                            'required' => 1,
                        ]
                    ]
                ]
            ],
            PageRepository::DOKTYPE_PERSON => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT]['showitem'],
            ]
        ]
    ]
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;;postTitle',
    PageRepository::DOKTYPE_POST,
    'replace:--palette--;;title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'page_persons',
    PageRepository::DOKTYPE_PERSON,
    'before:--palette--;;title'
);

ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'php_tree_stop',
    254,
    'after:module'
);
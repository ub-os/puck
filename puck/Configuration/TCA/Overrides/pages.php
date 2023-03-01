<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use UBOS\Puck\Constants;

// cropVariants
$cropVariants = require(ExtensionManagementUtility::extPath('puck') . 'Configuration/TCA/Common/CropVariants.php');

$GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    '3:2' => $cropVariants['3:2'],
    '16:9' => $cropVariants['16:9'],
    'social' => [
        'title' => '1.91:1',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '1.91:1',
                'value' => 1200/630
            ]
        ]
    ]
];
$GLOBALS['TCA']['pages']['columns']['og_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'];
$GLOBALS['TCA']['pages']['columns']['twitter_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'];



// columns
$GLOBALS['TCA']['pages']['columns']['icon'] = require ExtensionManagementUtility::extPath('puck') .'/Configuration/TCA/Common/Columns/Icon.php';

$GLOBALS['TCA']['pages']['columns']['teaser_text'] = [
    'label' => 'Teaser text',
    'config' => $GLOBALS['TCA']['pages']['columns']['abstract']['config'],
];

$GLOBALS['TCA']['pages']['columns']['post_date'] = [
    'label' => 'Date',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputDateTime',
        'size' => 16,
        'eval' => 'datetime',
    ],
];
$GLOBALS['TCA']['pages']['columns']['post_author'] = [
    'label' => 'Author',
    'config' => [
        'type' => 'group',
        'allowed' => 'tx_puck_domain_model_person',
        'size' => 1,
        'maxitems' => 1
    ],
];
$GLOBALS['TCA']['pages']['columns']['page_persons'] = [
    'label' => 'Person',
    'config' => [
        'required' => '1',
        'type' => 'group',
        'allowed' => 'tx_puck_domain_model_person',
        'foreign_table' => 'tx_puck_domain_model_person',
        'MM' => 'tx_puck_person_page_mm',
        'MM_opposite_field' => 'pages',
        'size' => 1,
        'maxitems' => 1
    ],
];



// palettes
$GLOBALS['TCA']['pages']['palettes']['title']['showitem'] = '
    title,--linebreak--,slug,--linebreak--,nav_title,--linebreak--,subtitle,--linebreak--,teaser_text';
$GLOBALS['TCA']['pages']['palettes']['media']['showitem'] = '
    media,--linebreak--, icon';
$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    'Post folder',
    'posts',
    'blog_post',
];
$GLOBALS['TCA']['pages']['palettes']['postTitle'] = [
    'label' => $GLOBALS['TCA']['pages']['palettes']['title']['label'],
    'showitem' => 'title,--linebreak--,slug,--linebreak--,nav_title,--linebreak--,subtitle,--linebreak--,post_date,lastUpdated,--linebreak--,teaser_text, post_author'
];


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
                'showitem' => $GLOBALS['TCA']['pages']['types'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT]['showitem'],
                'columnsOverrides' => [
                    'post_date' => [
                        'config' => [
                            'required' => 1,
                        ]
                    ]
                ]
            ],
            Constants::DOKTYPE_PERSON => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT]['showitem'],
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
<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\TcaUtility;
use UBOS\Puck\UserFunctions\FormEngine\PageItemsProcFunc;

$GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = TcaUtility::getCropVariants('3:2,16:9,191:100');
$GLOBALS['TCA']['pages']['columns']['og_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = TcaUtility::getCropVariants('191:100');
$GLOBALS['TCA']['pages']['columns']['twitter_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = TcaUtility::getCropVariants('16:9');

array_push(
    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'],
    [
        'News folder',
        'news',
        'news_page',
    ],
    [
        'Category folder',
        'categories',
        'mimetypes-x-sys_category',
    ],
    [
        'Person folder',
        'persons',
        'person',
    ]
);


$GLOBALS['TCA']['pages']['columns']['icon'] = require ExtensionManagementUtility::extPath('puck') .'/Configuration/TCA/Helper/IconField.php';

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
        'foreign_table' => 'tx_puck_domain_model_person',
        'size' => 1,
        'relationship' => 'oneToOne'
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

$GLOBALS['TCA']['pages']['columns']['doktype']['config']['itemsProcFunc'] = PageItemsProcFunc::class . '->doktype';
$GLOBALS['TCA']['pages']['columns']['doktype']['config']['disableNoMatchingValueElement'] = true;

$GLOBALS['TCA']['pages']['columns']['backend_layout']['config'] = array_merge(
    $GLOBALS['TCA']['pages']['columns']['backend_layout']['config'],
    [
        'itemsProcFunc' => PageItemsProcFunc::class . '->backendLayout',
        'items' => [],
        'default' => 'pagets__default',
        'disableNoMatchingValueElement' => true,
    ]
);
$GLOBALS['TCA']['pages']['columns']['backend_layout_next_level']['config'] = array_merge(
    $GLOBALS['TCA']['pages']['columns']['backend_layout_next_level']['config'],
    [
        'itemsProcFunc' => '',
        'items' => [
            ['label' => 'default', 'value' => 'pagets__default']
        ],
        'default' => 'pagets__default',
        'disableNoMatchingValueElement' => true,
    ]
);

$GLOBALS['TCA']['pages']['columns']['breadcrumb_title'] = [
    'label' => 'Breadcrumb title',
    'config' => $GLOBALS['TCA']['pages']['columns']['nav_title']['config'],
];

$GLOBALS['TCA']['pages']['columns']['teaser_title'] = [
    'label' => 'Teaser title',
    'config' => $GLOBALS['TCA']['pages']['columns']['nav_title']['config'],
];

$GLOBALS['TCA']['pages']['columns']['teaser_text'] = [
    'label' => 'Teaser text',
    'config' => $GLOBALS['TCA']['pages']['columns']['abstract']['config'],
];

$GLOBALS['TCA']['pages']['columns']['teasers'] = [
    'label' => 'Teasers',
    'description' => 'Teaser records can be selected in menu elements alongside their respective pages, overriding the page\'s teaser content for that menu element.',
    'config' => [
        'type' => 'inline',
        'foreign_field' => 'page',
        'foreign_table' => 'tx_puck_domain_model_page_teaser',
        'foreign_table_where' => 'AND tx_puck_domain_model_page_teaser.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)',
        'foreign_table_field' => 'parent_table',
        'foreign_sortby' => 'sorting',
        'maxitems' => '30',
        'minitems' => '0',
        'appearance' => [
            'collapseAll' => '1',
            'enabledControls' => [
                'info' => true,
                'new' => true,
                'dragdrop' => true,
                'sort' => true,
                'hide' => true,
                'delete' => true,
                'localize' => true,
            ],
            'levelLinksPosition' => 'bottom',
            'useSortable' => '1',
        ],
    ],
];

// multiselect for webpagetype messes with the schema auto generation of ext:schema,
// so it has to be disabled in settings if we want to use multi types
$GLOBALS['TCA']['pages']['columns']['tx_schema_webpagetype']['config'] = [
    'items' => $GLOBALS['TCA']['pages']['columns']['tx_schema_webpagetype']['config']['items'],
    'itemsProcFunc' => $GLOBALS['TCA']['pages']['columns']['tx_schema_webpagetype']['config']['itemsProcFunc'],
    'type' => 'select',
    'renderType' => 'selectMultipleSideBySide',
    'size' => 3,
    'autoSizeMax' => 10,
];

// fixes issue where categories are not translated https://forge.typo3.org/issues/97526
$GLOBALS['TCA']['pages']['columns']['categories']['config']['behaviour'] = [
    'allowLanguageSynchronization' => true
];
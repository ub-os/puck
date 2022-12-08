<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Theme\UserFunctions\FormEngine\ContentItemsProcFunc;

$GLOBALS['TCA']['tt_content']['columns']['frame_class'] = [
    'label' => 'Appearance',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
        ],
        'default' => 'default'
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['layout'] = [
    'label' => 'Layout',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
            ['6 Columns centered', 'cols6'],
            ['8 columns centered', 'cols8'],
            ['10 columns centered', 'cols10'],
            ['Text/Media 5/5', 'cols5-5'],
            ['Text/Media 6/4', 'cols6-4'],
            ['Home', 'stage-home'],
        ],
        'default' => 'default'
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['header_layout'] = [
    'label' => 'Headline Type',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['H2', 0],
            ['H2 in H3-style', 1],
            ['H3', 2],
            ['Paragraph', 3],
        ],
        'default' => 0
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['imagecols'] = [
    'label' => 'Media per Row',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['1 (8 columns wide)', 1],
            ['1 (10 columns wide)', 11],
            ['2', 2],
            ['3', 3],
            ['4', 4],
            ['5', 5],
        ],
        'default' => 1
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['content_type'] = [
    'label' => 'Content type',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Media files', 'assets'],
            ['Page teaser', 'page'],
            ['Product teaser', 'product'],
            ['HTML', 'html'],
        ],
        'default' => 'assets'
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['imageorient'] = [
    'label' => 'Media position',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['default', 0],
            ['Above text', 5,
                'imageorient_top-center',
            ],
            ['Below text', 6,
                'imageorient_bottom-center',
            ],
            ['Right beside text', 3,
                'imageorient_right-top'
            ],
            ['Left beside text', 4,
                'imageorient_left-top'
            ],
            ['Right in text', 1,
                'imageorient_right-float'
            ],
            ['Left in text', 2,
                'imageorient_left-float'
            ],

        ],
        'default' => 1,
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ]
];


$GLOBALS['TCA']['tt_content']['columns']['pages'] = [
    'label' => 'Pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['parents'] = [
    'label' => 'Parent pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50,
    ],
];


$GLOBALS['TCA']['tt_content']['columns']['space_before_class'] = [
    'label' => 'Space Before',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Auto', '',
                'space_auto'
            ],
            ['None', 'none',
                'space_none'
            ],
            ['Small', 'small',
                'space_small'
            ],
            ['Medium', 'medium',
                'space_medium'
            ],
            ['Large', 'large',
                'space_large'
            ],
        ],
        'default' => '',
    ]
];
$GLOBALS['TCA']['tt_content']['columns']['space_after_class'] = [
    'label' => 'Space After',
    'config' => $GLOBALS['TCA']['tt_content']['columns']['space_before_class']['config']
];

$GLOBALS['TCA']['tt_content']['columns']['bodytext2'] = $GLOBALS['TCA']['tt_content']['columns']['bodytext'];

$iconJson = file_get_contents(ExtensionManagementUtility::extPath('theme') . "Resources/Public/Fonts/Icons/icons.json");

$iconJsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($iconJson, TRUE)),
    RecursiveIteratorIterator::SELF_FIRST);
$iconSelectItems = [['none', '']];
foreach ($iconJsonIterator as $key => $val) {
    $iconSelectItems[] = [
        $key, $key, 'EXT:theme/Resources/Public/Icons/Frontend/' . $key . '.svg'
    ];
}
/*$GLOBALS['TCA']['tt_content']['columns']['icon'] = [
    'exclude' => true,
    'label' => 'Icon',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $iconSelectItems,
        'default' => '',
    ]
];*/

$GLOBALS['TCA']['tt_content']['columns']['inline_media'] = [
    'label' => 'Content items',
    'config' => [
        'appearance' => [
            'collapseAll' => '1',
            'enabledControls' => [
                'dragdrop' => '1',
            ],
            'levelLinksPosition' => 'bottom',
            'useSortable' => '1',
        ],
        'foreign_field' => 'parent_uid',
        'foreign_table' => 'tx_theme_domain_model_inline_media',
        'foreign_table_field' => 'parent_table',
        'maxitems' => '30',
        'minitems' => '0',
        'type' => 'inline',
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['container_width'] = [
    'label' => 'Width',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['4', 4],
            ['5', 5],
            ['6', 6],
            ['7', 7],
            ['8', 8],
            ['9', 9],
            ['10', 10],
            ['11', 11],
            ['12', 12],
        ],
        'default' => 10
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['item_column_width'] = [
    'label' => 'Default item width',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => ContentItemsProcFunc::class.'->itemColumnWidth',
        'disableNoMatchingValueElement' => true,
        'items' => [
            ['2', 2],
            ['3', 3],
            ['4', 4],
            ['5', 5],
            ['6', 6],
            ['7', 7],
            ['8', 8],
            ['9', 9],
            ['10', 10],
            ['11', 11],
            ['12', 12],
        ],
        'default' => 5
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['column_position'] = [
    'label' => 'Align items',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Space between', 'space-between'],
            ['Center', 'center'],
            ['Left', 'left'],
            ['Right', 'right'],
        ],
        'default' => 'space-between'
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['text_column_width'] = $GLOBALS['TCA']['tt_content']['columns']['item_column_width'];
$GLOBALS['TCA']['tt_content']['columns']['media_column_width'] = $GLOBALS['TCA']['tt_content']['columns']['item_column_width'];
$GLOBALS['TCA']['tt_content']['columns']['text_column_width']['label'] = 'Text width';
$GLOBALS['TCA']['tt_content']['columns']['media_column_width']['label'] = 'Media width';
$GLOBALS['TCA']['tt_content']['columns']['text_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->textColumnWidth';
$GLOBALS['TCA']['tt_content']['columns']['media_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->mediaColumnWidth';


$GLOBALS['TCA']['tt_content']['columns']['container_position'] = [
    'label' => 'Align',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Center', 'center'],
            ['Left', 'left'],
            ['Right', 'right'],
        ],
        'default' => 'center'
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['container_offset'] = [
    'label' => 'Offset',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => ContentItemsProcFunc::class.'->containerOffset',
        'disableNoMatchingValueElement' => true,
        'items' => [
            ['0', 0],
            ['1', 1],
            ['2', 2],
            ['3', 3],
            ['4', 4]
        ],
        'default' => 0,
    ],
];
$GLOBALS['TCA']['tt_content']['palettes']['gridContainer'] = [
    'label' => 'Grid container',
    'showitem' => '
        container_width, container_position, container_offset'
];
$GLOBALS['TCA']['tt_content']['palettes']['gridColumns'] = [
    'label' => 'Grid columns',
    'showitem' => '
        item_column_width, column_position'
];
$GLOBALS['TCA']['tt_content']['palettes']['gridMedia'] = [
    'label' => 'Grid columns',
    'showitem' => '
        imageorient,
        --linebreak--,
        text_column_width, media_column_width,
        --linebreak--,
        item_column_width, column_position'
];
$GLOBALS['TCA']['tt_content']['palettes']['layout'] = [
    'label' => 'Configuration',
    'showitem' => '
        layout'
];
$GLOBALS['TCA']['tt_content']['palettes']['layout_frame_class'] = [
    'label' => 'Configuration',
    'showitem' => '
        frame_class, layout'
];
$GLOBALS['TCA']['tt_content']['palettes']['layout_full'] = [
    'label' => 'Configuration',
    'showitem' => '
        frame_class, layout, --linebreak--,
        space_before_class, space_after_class'
];
$GLOBALS['TCA']['tt_content']['palettes']['headers'] = [
    'label' => 'Headlines',
    'showitem' => '
        header,--linebreak--,
        header_layout, header_position,--linebreak--,
        subheader'
];
$GLOBALS['TCA']['tt_content']['palettes']['bodytext'] = [
    'showitem' => 'bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel',
];
$GLOBALS['TCA']['tt_content']['palettes']['menu_pages'] = [
    'showitem' => 'pages; Selected pages, parents; Parent pages',
    'canNotCollapse' => 1
];
$GLOBALS['TCA']['tt_content']['palettes']['media_config'] = [
    'label' => 'Configuration',
    'showitem' => '
        imageorient;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:imageorient_formlabel,
        imagecols;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:imagecols_formlabel,'
];
$GLOBALS['TCA']['tt_content']['palettes']['media'] = [
    'showitem' => 'media',
];

// header fields simplified as one rich text field?
/*$GLOBALS['TCA']['tt_content']['columns']['header'] = [
    'l10n_mode' => 'prefixLangTitle',
    'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header',
    'config' => [
        'type' => 'text',
        'cols' => 50,
        'rows' => 1,
        'enableRichtext' => true,
        'richtextConfiguration' => 'header',
    ],
];*/

$baseShowItem = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,';

require_once ExtensionManagementUtility::extPath('theme') .'/Configuration/Helper/getContentClasses.php';
foreach(getContentClasses() as $content) {
    $class = new $content['fullName'];
    $GLOBALS['TCA']['tt_content']['types'][$content['ctype']]['showitem'] = $class->showItem().$baseShowItem;
    $GLOBALS['TCA']['tt_content']['types'][$content['ctype']]['columnsOverrides'] = $class->columnsOverrides();
}
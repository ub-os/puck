<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Theme\UserFunctions\FormEngine\ContentItemsProcFunc;

$GLOBALS['TCA']['tt_content']['columns']['frame_class'] = [
    'label' => 'Background',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
            ['Dark', 'dark'],
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
            ['1', 1],
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
        'disableNoMatchingValueElement' => true,
        'items' => [
            ['default', 0],
            ['Above text', 5,
                'image_orient_top-center',
            ],
            ['Below text', 6,
                'image_orient_bottom-center',
            ],
            ['Right beside text', 3,
                'image_orient_right-top'
            ],
            ['Left beside text', 4,
                'image_orient_left-top'
            ],
            ['Right in text', 1,
                'image_orient_right-float'
            ],
            ['Left in text', 2,
                'image_orient_left-float'
            ],

        ],
        'default' => 6,
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

$GLOBALS['TCA']['tt_content']['columns']['icon'] = require ExtensionManagementUtility::extPath('theme') .'/Configuration/TCA/Common/Columns/Icon.php';

$GLOBALS['TCA']['tt_content']['columns']['inline_media'] = [
    'label' => 'Content items',
    'config' => [
        'type' => 'inline',
        'foreign_field' => 'parent_uid',
        'foreign_table' => 'tx_theme_domain_model_inline_media',
        'foreign_table_field' => 'parent_table',
        'foreign_sortby' => 'sorting',
        'maxitems' => '30',
        'minitems' => '0',
        'appearance' => [
            'collapseAll' => '1',
            'enabledControls' => [
                'dragdrop' => '1',
            ],
            'levelLinksPosition' => 'bottom',
            'useSortable' => '1',
        ],
    ],
];
$GLOBALS['TCA']['tt_content']['columns']['container_width'] = [
    'label' => 'Width',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['4', 4, 'column_width4'],
            ['5', 5, 'column_width5'],
            ['6', 6, 'column_width6'],
            ['7', 7, 'column_width7'],
            ['8', 8, 'column_width8'],
            ['9', 9, 'column_width9'],
            ['10', 10, 'column_width10'],
            ['11', 11, 'column_width11'],
            ['12', 12, 'column_width12'],
        ],
        'default' => 12,
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
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
            ['2', 2, 'column_width2'],
            ['3', 3, 'column_width3'],
            ['4', 4, 'column_width4'],
            ['5', 5, 'column_width5'],
            ['6', 6, 'column_width6'],
            ['7', 7, 'column_width7'],
            ['8', 8, 'column_width8'],
            ['9', 9, 'column_width9'],
            ['10', 10, 'column_width10'],
            ['11', 11, 'column_width11'],
            ['12', 12, 'column_width12'],
        ],
        'default' => 5,
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['text_column_width'] = $GLOBALS['TCA']['tt_content']['columns']['item_column_width'];
$GLOBALS['TCA']['tt_content']['columns']['media_column_width'] = $GLOBALS['TCA']['tt_content']['columns']['item_column_width'];
$GLOBALS['TCA']['tt_content']['columns']['text_column_width']['label'] = 'Text width';
$GLOBALS['TCA']['tt_content']['columns']['media_column_width']['label'] = 'Media width';
$GLOBALS['TCA']['tt_content']['columns']['text_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->textColumnWidth';
$GLOBALS['TCA']['tt_content']['columns']['media_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->mediaColumnWidth';

$GLOBALS['TCA']['tt_content']['columns']['column_position'] = [
    'label' => 'Align items',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Space between', 'space-between', 'space-between'],
            ['Left', 'left', 'left'],
            ['Center', 'center', 'center'],
            ['Right', 'right', 'right'],
        ],
        'default' => 'space-between',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];


$GLOBALS['TCA']['tt_content']['columns']['container_position'] = [
    'label' => 'Align',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Left', 'left', 'left'],
            ['Center', 'center', 'center'],
            ['Right', 'right', 'right'],
        ],
        'default' => 'center',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
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
$GLOBALS['TCA']['tt_content']['columns']['card_media_size'] = [
    'label' => 'Media size',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Full-width / full-height', 'cover', 'cover'],
            ['Contained', 'contain', 'contain'],
        ],
        'default' => 'cover',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ]
];
$GLOBALS['TCA']['tt_content']['columns']['media_max_height'] = [
    'label' => 'Maximum height',
    'description' => 'Maximum image height for mobile devices (in % of viewport height)',
    'config' => [
        'type' => 'input',
        'size' => 30,
        'eval' => 'int',
        'default' => 0,
        'range' => [
            'lower' => 0,
            'upper' => 100
        ],
        'valuePicker' => [
            'items' => [
                ['20', 20],
                ['30', 30],
                ['40', 40],
                ['50', 50]
            ],
        ],
    ],
];
$GLOBALS['TCA']['tt_content']['palettes']['gridContainer'] = [
    'label' => 'Grid container',
    'showitem' => '
        container_width, container_position, container_offset'
];
$GLOBALS['TCA']['tt_content']['palettes']['gridContainerWidth'] = [
    'label' => 'Grid container',
    'showitem' => '
        container_width'
];
$GLOBALS['TCA']['tt_content']['palettes']['gridColumns'] = [
    'label' => 'Layout',
    'showitem' => '
        item_column_width, column_position'
];
$GLOBALS['TCA']['tt_content']['palettes']['gridMedia'] = [
    'label' => 'Layout',
    'showitem' => '
        imageorient,
        text_column_width, media_column_width,
        --linebreak--,
        item_column_width, column_position, media_max_height'
];
$GLOBALS['TCA']['tt_content']['palettes']['gridCard'] = [
    'label' => 'Layout',
    'showitem' => '
        imageorient, card_media_size, media_column_width'
];
$GLOBALS['TCA']['tt_content']['palettes']['appearance'] = [
    'label' => 'Appearance',
    'showitem' => '
        frame_class'
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

// add overrides from Domain Model Content Classes
$baseShowItem = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,';

;
foreach(require ExtensionManagementUtility::extPath('theme') .'/Configuration/Helper/getContentClasses.php' as $content) {
    $class = new $content['fullName'];
    $GLOBALS['TCA']['tt_content']['types'][$content['ctype']]['showitem'] = $class->showItem().$baseShowItem;
    $GLOBALS['TCA']['tt_content']['types'][$content['ctype']]['columnsOverrides'] = $class->columnsOverrides();
}
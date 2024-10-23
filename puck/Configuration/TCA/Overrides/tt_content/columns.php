<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc;
use UBOS\Puck\Utility\TcaUtility;

$columns = [];

$columns['frame_class'] = [
    'label' => 'Background',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Default', 'default'],
            ['Dark purple', 'dark-1'],
        ]),
        'default' => 'default'
    ],
];

$columns['layout'] = [
    'label' => 'Appearance type',
    'onChange' => 'reload',
    'disableNoMatchingValueElement' => true,
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Default', 'default'],
        ]),
        'disableNoMatchingValueElement' => true,
        'default' => 'default'
    ],
];

$columns['header_layout'] = [
    'label' => 'Headline Type',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            // 0 = default
            ['H2', 0],

            // 20-29 reserved for h2 styles, these styles increase spacing to preceeding element
            // for example: ['H2 alternative color', 21],

            // 25-29 reserved for h2 styles that enable subheader
            ['H2 in topline style', 25],

            // 30-39 reserved for h3 styles
            ['H3', 30],
            ['H2 in H3-style', 31],

            ['Paragraph', 50],

            // 100 is hidden and for backend only
            ['Hidden', 100],
        ]),
        'default' => 0
    ],
];
$columns['subheader'] = [
    'label' => $GLOBALS['TCA']['tt_content']['columns']['subheader']['label'],
    'config' => $GLOBALS['TCA']['tt_content']['columns']['subheader']['config'],
    'displayCond' => [
        'AND' => [
            'FIELD:header_layout:!=:100',
            'FIELD:header_layout:>:24',
            'FIELD:header_layout:<:30',
        ],
    ],
];

$columns['bodytext'] = $GLOBALS['TCA']['tt_content']['columns']['bodytext'];
$columns['bodytext']['config']['search']['andWhere'] = '';

$columns['imagecols'] = [
    'label' => 'Media per Row',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['1', 1],
            ['2', 2],
            ['3', 3],
            ['4', 4],
            ['5', 5],
        ]),
        'default' => 1
    ],
];
$columns['media_layout'] = [
    'label' => 'Media layout',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Below text', 'below',
                'media_layout_below',
            ],
            ['Above text', 'above',
                'media_layout_above',
            ],
            ['Right beside text', 'right',
                'media_layout_right',
            ],
            ['Left beside text', 'left',
                'media_layout_left',
            ],
            ['Right in text', 'right-float',
                'media_layout_right_float',
            ],
            ['Left in text', 'left-float',
                'media_layout_left_float'
            ],

        ]),
        'itemsProcFunc' => ContentItemsProcFunc::class . '->mediaLayout',
        'dbFieldLength' => 255,
        'default' => 'below',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ]
];

$columns['pages'] = [
    'label' => 'Pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50
    ],
];

$columns['header_spacing_override'] =  [
    'label' => 'Force spacing',
    'description' => 'Force increased distance to preceding element, even if no h2-style headline is set.',
    'config' => [
        'type' => 'check',
    ],
];

$columns['space_before_class'] = [
    'label' => 'Space Before',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Auto', '',
                'auto'
            ],
            ['None', 'none',
                'none'
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
        ]),
        'default' => '',
    ]
];
$columns['space_after_class'] = [
    'label' => 'Space After',
    'config' => $columns['space_before_class']['config']
];
$columns['assets'] = $GLOBALS['TCA']['tt_content']['columns']['assets'];
$columns['assets']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    'default' => TcaUtility::getCropVariant('Default','standard'),
];

$columns['icon'] = require ExtensionManagementUtility::extPath('puck') .'/Configuration/TCA/Helper/IconField.php';

$columnWidthItems = [
    ['12', 12, 'column_width12'],
    ['11', 11, 'column_width11'],
    ['10', 10, 'column_width10'],
    ['9', 9, 'column_width9'],
    ['8', 8, 'column_width8'],
    ['7', 7, 'column_width7'],
    ['6', 6, 'column_width6'],
    ['5', 5, 'column_width5'],
    ['4', 4, 'column_width4'],
    ['3', 3, 'column_width3'],
    ['2', 2, 'column_width2'],
];

$columns['container_width'] = [
    'label' => 'Width',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'itemsProcFunc' => ContentItemsProcFunc::class.'->containerWidth',
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper($columnWidthItems),
        'default' => 12,
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];
$columns['item_column_width'] = [
    'label' => 'Default item width',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => ContentItemsProcFunc::class.'->itemColumnWidth',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper($columnWidthItems),
        'default' => 6,
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];

$columns['text_column_width'] = $columns['item_column_width'];
$columns['media_column_width'] = $columns['item_column_width'];
$columns['text_column_width']['label'] = 'Text width';
$columns['media_column_width']['label'] = 'Media gallery width';
$columns['text_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->textColumnWidth';
$columns['media_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->mediaColumnWidth';

$columns['row_justify'] = [
    'label' => 'Align horizontally',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Left', 'left', 'align_left'],
            ['Center', 'center', 'align_center'],
            ['Right', 'right', 'align_right'],
            ['Space between', 'space-between', 'align_space_between'],
        ]),
        'default' => 'left',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];
$columns['row_align'] = [
    'label' => 'Align vertically',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Top', 'start', 'align_top'],
            ['Center', 'center', 'align_center_vertical'],
            ['Bottom', 'end', 'align_bottom'],
            ['Stretch', 'stretch', 'align_stretch']
        ]),
        'default' => 'top',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];
$columns['container_position'] = [
    'label' => 'Align',
    'onChange' => 'reload',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Left', 'left', 'align_left'],
            ['Center', 'center', 'align_center'],
            ['Right', 'right', 'align_right'],
        ]),
        'default' => 'center',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ],
];
$columns['container_offset'] = [
    'label' => 'Offset',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => ContentItemsProcFunc::class.'->containerOffset',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['0', 0],
            ['1', 1],
            ['2', 2],
            ['3', 3],
            ['4', 4]
        ]),
        'default' => 0,
    ],
];
$columns['card_media_size'] = [
    'label' => 'Media size',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Full-width / full-height', 'cover', 'size_cover'],
            ['Contained', 'contain', 'size_contain'],
            ['Background', 'background'],
        ]),
        'default' => 'cover',
        'fieldWizard' => [
            'selectIcons' => [
                'disabled' => false,
            ],
        ],
    ]
];
$columns['media_max_height'] = [
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
$columns['menu_item_config'] = [
    'label' => 'Teaser content',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectMultipleSideBySide',
        'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
            ['Teaser text', 'teaserText'],
            ['Subtitle', 'subtitle'],
            ['Media', 'media'],
            ['Category', 'category'],
            ['Author', 'author'],
            ['Date (lastUpdated)', 'lastUpdated'],
            ['Icon', 'icon'],
            ['Call to action', 'cta']
        ]),
        'default' => 'teaserText,media',
    ]
];
$columns['flex_grow'] = [
    'label' => 'Grow items',
    'description' => 'Items will grow to fill the available space',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
    ]
];
$columns['options'] = [
    'label' => 'Options',
    'config' => [
        'type' => 'flex',
        'ds' => [
            'default' => $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['default'],
        ],
        'ds_pointerField' => 'layout,CType',
    ]
];
foreach($columns as $name => $column) {
    $GLOBALS['TCA']['tt_content']['columns'][$name] = $column;
}
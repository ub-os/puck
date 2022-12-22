<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Theme\UserFunctions\FormEngine\ContentItemsProcFunc;

$cropVariants = require __DIR__.'/../Common/CropVariants.php';

$columns = [];

$columns['frame_class'] = [
    'label' => 'Background',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
            ['Light blue', 'light-blue'],
            ['Light turquoise', 'light-turquoise'],
            ['Light orange', 'light-orange'],
            ['Dark purple', 'dark-purple'],
        ],
        'default' => 'default'
    ],
];

$columns['layout'] = [
    'label' => 'Layout',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
        ],
        'default' => 'default'
    ],
];

$columns['header_layout'] = [
    'label' => 'Headline Type',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            // 0 = default
            ['H2', 0],

            // 20-29 reserved for h2 styles, these styles increase spacing to preceeding element
            // for example: ['H2 alternative color', 21],

            // 30-39 reserved for h3 styles
            ['H3', 30],
            ['H2 in H3-style', 31],

            ['Paragraph', 50],
        ],
        'default' => 0
    ],
];

$columns['imagecols'] = [
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
$columns['content_type'] = [
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
$columns['imageorient'] = [
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


$columns['pages'] = [
    'label' => 'Pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50
    ],
];

$columns['parents'] = [
    'label' => 'Parent pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50,
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
$columns['space_after_class'] = [
    'label' => 'Space After',
    'config' => $columns['space_before_class']['config']
];
$columns['assets'] = $GLOBALS['TCA']['tt_content']['columns']['assets'];
$columns['assets']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    'default' => $cropVariants['default'],
    'mobile' => $cropVariants['mobile'],
];
$columns['bodytext2'] = $GLOBALS['TCA']['tt_content']['columns']['bodytext'];

$columns['icon'] = require ExtensionManagementUtility::extPath('theme') .'/Configuration/TCA/Common/Columns/Icon.php';

$columns['inline_media'] = [
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
$columns['container_width'] = [
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
$columns['item_column_width'] = [
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

$columns['text_column_width'] = $columns['item_column_width'];
$columns['media_column_width'] = $columns['item_column_width'];
$columns['text_column_width']['label'] = 'Text width';
$columns['media_column_width']['label'] = 'Media width';
$columns['text_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->textColumnWidth';
$columns['media_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class.'->mediaColumnWidth';

$columns['column_position'] = [
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


$columns['container_position'] = [
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
$columns['container_offset'] = [
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
$columns['card_media_size'] = [
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

return $columns;
<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme') .'/Configuration/_Content/_CTypes/CTypesTCA.php';
$files = glob( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme').'/Configuration/_Content/*/*TCA.php' );
foreach ( $files as $file )
  require_once $file;

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

$GLOBALS['TCA']['tt_content']['columns']['frame_class'] = [
    'label' => 'Appearance',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
            ['Blue background', 'blue-bg'],
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
            ['8 columns centered', 'cols10'],
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
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['default', 0],
            ['Right in text', 1,
                'content-inside-text-img-right'
            ],
            ['Left in text', 2,
                'content-inside-text-img-left'
            ],
            ['Right beside text', 3,
                'content-beside-text-img-right'
            ],
            ['Left beside text', 4,
                'content-beside-text-img-left'
            ],
            ['Above text', 5,
                'content-beside-text-img-above-center',
            ],
            ['Below text', 6,
                'content-beside-text-img-below-center',
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


$GLOBALS['TCA']['tt_content']['columns']['pages'] =  [
    'exclude' => true,
    'label' => 'Pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50,
    ],
];

$GLOBALS['TCA']['tt_content']['columns']['parents'] =  [
    'exclude' => true,
    'label' => 'Pages',
    'config' => [
        'type' => 'group',
        'allowed' => 'pages',
        'size' => 3,
        'maxitems' => 50,
    ],
];



/*$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['fieldWizard'] = [
    'selectIcons' => [
        'disabled' => false,
    ],
];*/

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
$GLOBALS['TCA']['tt_content']['columns']['frame_class2'] = $GLOBALS['TCA']['tt_content']['columns']['frame_class'];

$GLOBALS['TCA']['tt_content']['columns']['assets2'] = $GLOBALS['TCA']['tt_content']['columns']['assets'];
$GLOBALS['TCA']['tt_content']['columns']['assets2']['config']['foreign_match_fields']['fieldname'] = 'assets2';

$iconJson = file_get_contents(ExtensionManagementUtility::extPath('theme')."Resources/Public/fonts/icons/icons.json");

$iconJsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($iconJson, TRUE)),
    RecursiveIteratorIterator::SELF_FIRST);
$iconSelectItems = [['none', '']];
foreach ($iconJsonIterator as $key => $val) {
    $iconSelectItems[] = [
      $key, $key, 'EXT:theme/Resources/Public/images/icons/'.$key.'.svg'
    ];
}
$GLOBALS['TCA']['tt_content']['columns']['icon'] =  [
    'exclude' => true,
    'label' => 'Icon',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $iconSelectItems,
        'default' => '',
    ]
];


$inline_itemsTca = [
    'inline_textmedia' => [
        'exclude' => true,
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
            'foreign_table' => 'tx_theme_domain_model_inline_textmedia',
            'foreign_table_field' => 'parent_table',
            'maxitems' => '30',
            'minitems' => '0',
            'type' => 'inline',
        ],
    ],
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    $inline_itemsTca
);
$GLOBALS['TCA']['tt_content']['palettes']['inline_items'] = [
    'showitem' => 'inline_items; Items',
    'canNotCollapse' => 1
];
$GLOBALS['TCA']['tt_content']['palettes']['media'] = [
    'showitem' => 'media',
];
$GLOBALS['TCA']['tt_content']['palettes']['bodytext'] = [
    'showitem' => 'bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel',
];
$GLOBALS['TCA']['tt_content']['palettes']['frames'] = [
    'label' => 'Configuration',
    'showitem' => '
        frame_class, frame_class2, layout, --linebreak--,
        space_before_class, space_after_class'
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

<?php

use UBOS\Theme\UserFunctions\FormEngine\InlineMediaItemsProcFunc;
$columnAssets = $GLOBALS['TCA']['tt_content']['columns']['assets'];
$columns = [
    'sys_language_uid' => [
        'exclude' => true,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'special' => 'languages',
            'items' => [
                [
                    'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                    -1,
                    'flags-multiple'
                ]
            ],
            'default' => 0,
        ],
    ],
    'l10n_parent' => [
        'displayCond' => 'FIELD:sys_language_uid:>:0',
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'default' => 0,
            'items' => [
                ['', 0],
            ],
            'foreign_table' => 'tx_theme_domain_model_inline_media',
            'foreign_table_where' => 'AND {#tx_theme_domain_model_inline_media}.{#pid}=###CURRENT_PID### AND {#tx_theme_domain_model_inline_media}.{#sys_language_uid} IN (-1,0)',
        ],
    ],
    'l10n_diffsource' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'item_type' => [
        'label' => 'Type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'disableNoMatchingValueElement' => true,
            'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->itemType',
            'items' => [
                ['1', '1', 'inline_media'],
                ['Column', 'columns', 'columns_inline_media'],
                ['Card', 'cards', 'cards_inline_media'],
                ['Accordion', 'accordions', 'accordions_inline_media'],
                ['Stage carousel slide', 'stage_carousel', 'stage_carousel_inline_media'],
            ],
            'default' => '1'
        ]
    ],
    'hidden' => [
        'exclude' => true,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                    'invertStateDisplay' => true
                ]
            ],
        ],
    ],
    'header' => [
        'exclude' => true,
        'label' => 'Header',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'subheader' => [
        'exclude' => true,
        'label' => 'Subheader',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'bodytext' => [
        'label' => 'Text',
        'config' => [
            'type' => 'text',
            'enableRichtext' => 1,
        ],
    ],
    'assets' => $columnAssets,
    'icon' => require __DIR__.'/../Common/Columns/Icon.php',
    'column_width' => [
        'label' => 'Item width',
        'onChange' => 'reload',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->columnWidth',
            'disableNoMatchingValueElement' => true,
            'items' => [
                ['auto', 0, 'space_auto'],
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
            'default' => 0,
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false,
                ],
            ],
        ],
    ],
    'item_column_width' => [
        'label' => 'Default media item width',
        'onChange' => 'reload',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->itemColumnWidth',
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
    ],
    'column_position' => [
        'label' => 'Align items',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['Space between', 'space-between', 'space-between'],
                ['Center', 'center', 'center'],
                ['Left', 'left', 'left'],
                ['Right', 'right', 'right'],
            ],
            'default' => 'space-between',
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false,
                ],
            ],
        ]
    ],
    'imageorient' => [
        'label' => 'Media position',
        'onChange' => 'reload',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->imageorient',
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
    ],
    'media_max_height' => [
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
    ],
    'card_media_size' => [
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
    ],
    'frame_class' => $GLOBALS['TCA']['tt_content']['columns']['frame_class'],
    'layout' => $GLOBALS['TCA']['tt_content']['columns']['layout'],
    'parent_uid' => [
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    '',
                    0,
                ],
            ],
            'default' => 0,
            'foreign_table' => 'tt_content',
            'foreign_table_where' => 'AND tt_content.pid=###CURRENT_PID### AND tt_content.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)',
        ],
    ],
    'parent_table' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
];
$columns['media_column_width'] = $columns['item_column_width'];
$columns['text_column_width'] = $columns['item_column_width'];
$columns['text_column_width']['label'] = 'Text width';
$columns['media_column_width']['label'] = 'Media width';
$columns['text_column_width']['config']['itemsProcFunc'] = InlineMediaItemsProcFunc::class.'->textColumnWidth';
$columns['media_column_width']['config']['itemsProcFunc'] = InlineMediaItemsProcFunc::class.'->mediaColumnWidth';

return $columns;
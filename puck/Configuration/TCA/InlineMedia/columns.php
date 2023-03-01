<?php

use UBOS\Puck\UserFunctions\FormEngine\InlineMediaItemsProcFunc;
$columnAssets = $GLOBALS['TCA']['tt_content']['columns']['assets'];
$columns = [
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
                ['Hero carousel slide', 'hero_carousel', 'hero_carousel_inline_media'],
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
                ['auto', 0, 'auto'],
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
        'label' => 'Media item width',
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
    'row_justify' => [
        'label' => 'Align horizontally',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'disableNoMatchingValueElement' => true,
            'items' => [
                ['Left', 'left', 'align_left'],
                ['Center', 'center', 'align_center'],
                ['Right', 'right', 'align_right'],
                ['Space between', 'space-between', 'align_space_between'],
            ],
            'default' => 'left',
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false,
                ],
            ],
        ]
    ],
    'row_align' => [
        'label' => 'Align vertically',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'disableNoMatchingValueElement' => true,
            'items' => [
                ['Top', 'start', 'align_top'],
                ['Center', 'center', 'align_center_vertical'],
                ['Bottom', 'end', 'align_bottom'],
                ['Stretch', 'stretch', 'align_stretch']
            ],
            'default' => 'top',
            'fieldWizard' => [
                'selectIcons' => [
                    'disabled' => false,
                ],
            ],
        ],
    ],
    'media_layout' => [
        'label' => 'Media layout',
        'onChange' => 'reload',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->mediaLayout',
            'disableNoMatchingValueElement' => true,
            'items' => [
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
            ],
            'default' => 'below',
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
    'responsive_order' => [
        'label' => 'Responsive order',
        'description' => 'Order relative to other items on smaller screens only; Lower numbers are displayed first',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'int',
            'default' => 0,
            'range' => [
                'lower' => -10,
                'upper' => 10
            ],
        ],
    ],
    'card_media_size' => [
        'label' => 'Media size',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'disableNoMatchingValueElement' => true,
            'items' => [
                ['Full-width / full-height', 'cover', 'size_cover'],
                ['Contained', 'contain', 'size_contain'],
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
    'sys_language_uid' => [
        'exclude' => true,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
        'config' => [
            'type' => 'language',
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
            'foreign_table' => 'tx_puck_domain_model_inline_media',
            'foreign_table_where' => 'AND {#tx_puck_domain_model_inline_media}.{#pid}=###CURRENT_PID### AND {#tx_puck_domain_model_inline_media}.{#sys_language_uid} IN (-1,0)',
        ],
    ],
    'l10n_diffsource' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
];
$columns['media_column_width'] = $columns['item_column_width'];
$columns['text_column_width'] = $columns['item_column_width'];
$columns['text_column_width']['label'] = 'Text width';
$columns['media_column_width']['label'] = 'Media gallery width';
$columns['text_column_width']['config']['itemsProcFunc'] = InlineMediaItemsProcFunc::class.'->textColumnWidth';
$columns['media_column_width']['config']['itemsProcFunc'] = InlineMediaItemsProcFunc::class.'->mediaColumnWidth';

return $columns;
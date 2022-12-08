<?php
use UBOS\Theme\UserFunctions\FormEngine\InlineMediaItemsProcFunc;

return [
    'ctrl' => [
        'label' => 'header',
        'label_alt' => 'subheader,bodytext',
        'title' => 'Inline items',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'hideTable' => true,
        'iconfile' => 'EXT:theme/Resources/Public/Icons/Content/InlineMedia.svg',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'header',
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, header, subheader, bodytext',
    ],
    'columns' => [
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
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
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
        'assets' => $GLOBALS['TCA']['tt_content']['columns']['assets'],
        'column_width' => [
            'label' => 'Item width',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->columnWidth',
                'disableNoMatchingValueElement' => true,
                'items' => [
                    ['auto', 0],
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
                'default' => 0
            ],
        ],
        'item_column_width' => [
            'label' => 'Default item width',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->itemColumnWidth',
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
        ],
        'text_column_width' => [
            'label' => 'Text width',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->textColumnWidth',
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
        ],
        'media_column_width' => [
            'label' => 'Media width',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => InlineMediaItemsProcFunc::class.'->mediaColumnWidth',
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
        ],
        'column_position' => [
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
            ]
        ],
        'imageorient' => [
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
        ],
        'imagecols' => [
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
            'displayCond' => 'FIELD:imageorient:>:4',
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
    ],
    'palettes' => [
        'gridMedia' => [
            'label' => 'Grid columns',
            'showitem' => '
                    imageorient,
                    --linebreak--,
                    text_column_width, media_column_width,
                    --linebreak--,
                    item_column_width, column_position'
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
                    header, 
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    --palette--;;gridMedia,
                    assets,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
                    sys_language_uid, 
                    l10n_parent, 
                    l10n_diffsource, 
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
                    hidden'
        ],
    ],
];

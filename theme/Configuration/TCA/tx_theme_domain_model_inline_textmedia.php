<?php
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
        'iconfile' => 'EXT:theme/Resources/Public/images/ctype-icons/default/InlineTextMedia.svg',
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
                'foreign_table' => 'tx_theme_domain_model_inline_textmedia',
                'foreign_table_where' => 'AND {#tx_theme_domain_model_inline_textmedia}.{#pid}=###CURRENT_PID### AND {#tx_theme_domain_model_inline_textmedia}.{#sys_language_uid} IN (-1,0)',
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
        'imageorient' => [
            'label' => 'Media position',
            'onChange' => 'reload',
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
        'media_config' => [
            'label' => 'Configuration',
            'showitem' => '
                imageorient;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:imageorient_formlabel,
                imagecols;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:imagecols_formlabel,'
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
                    header, 
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    --palette--;;media_config,
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

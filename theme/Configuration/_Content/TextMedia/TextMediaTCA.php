<?php
$ctype = 'textmedia';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['showitem'] = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;frames,
        --palette--;;headers,
        --palette--;;bodytext,
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        --palette--;;media_config,
        assets,
        pages,
        bodytext2,
        content_type,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['columnsOverrides'] = [
    'bodytext' => [
        'config' => [
            'enableRichtext' => true,
        ],
    ],
    'assets' => [
        'displayCond' => 'FIELD:content_type:=:assets'
    ],
    'pages' => [
        'label' => 'Pages',
        'displayCond' => 'FIELD:content_type:=:page'
    ],
    'bodytext2' => [
        'label' => 'iFrame HTML',
        'config' => [
            'renderType' => 't3editor',
            'enableRichtext' => false,
        ],
        'displayCond' => 'FIELD:content_type:=:html'
    ],
    'imageorient' => [
        'onChange' => 'reload'
    ],
    'imagecols' => [
        'displayCond' => [
            'AND' => [
                'FIELD:content_type:=:assets',
                'FIELD:imageorient:>:4',
            ],
        ],
    ],
    'layout' => [
        'displayCond' => [
            'AND' => [
                'FIELD:content_type:=:assets',
                'FIELD:imageorient:<=:4',
            ],
        ],
    ],
];
$GLOBALS['TCA']['tt_content']['types'][$ctype]['columnsOverrides']['assets']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    '5:2' => ['disabled' => true],
    '3:2' => ['disabled' => true],
    '2:1' => ['disabled' => true],
    '1:1' => ['disabled' => true]
];

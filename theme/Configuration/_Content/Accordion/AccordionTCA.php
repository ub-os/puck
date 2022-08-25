<?php
$ctype = 'accordion';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['showitem'] = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;frames,
        --palette--;;headers,
        --palette--;;bodytext,        
    --div--;Items,
        inline_textmedia,
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
        ]
    ],
    'inline_textmedia' => [
        'label' => 'Accordion items',
        'config' => [
            'overrideChildTca' => [
                'columns' => [
                ],
            ]
        ]
    ]
];


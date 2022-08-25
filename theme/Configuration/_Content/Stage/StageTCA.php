<?php
$ctype = 'stage';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['showitem'] = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;frames,
        --palette--;;headers,
        --palette--;;bodytext,    
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        --palette--;;media_config,     
        assets,
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
];
$GLOBALS['TCA']['tt_content']['types'][$ctype]['columnsOverrides']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    '3:2' => ['disabled' => true],
    'default' => ['disabled' => true]
];

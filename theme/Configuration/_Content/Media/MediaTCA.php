<?php
$ctype = 'media';
$GLOBALS['TCA']['tt_content']['types'][$ctype]['showitem'] = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;frames,
        --palette--;;headers,
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
$GLOBALS['TCA']['tt_content']['types'][$ctype]['columnsOverrides'] = [];
$GLOBALS['TCA']['tt_content']['types'][$ctype]['columnsOverrides']['assets']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    '5:2' => ['disabled' => true],
    '2:1' => ['disabled' => true],
    '3:2' => ['disabled' => true],
    '1:1' => ['disabled' => true]
];


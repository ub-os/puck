<?php
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_child_hero_slide'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'assets' => TcaUtility::getCropVariantConfigOverride('2:1,3:2', 'default,mobile')
    ]
];
<?php
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_menu_anchors'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;headers,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];
<?php
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_menu_persons'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Plugin,
            pi_flexform,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => [
                    ['Team cards', 'team-cards'],
                ],
                'default' => 'team-cards'
            ]
        ]
    ]
];
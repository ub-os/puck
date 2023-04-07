<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_menu_posts
 */
return [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;appearanceLayout,
        --palette--;;headers,
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
                    ['Blog cards', 'blog-cards'],
                ],
                'default' => 'blog-cards'
            ]
        ]
    ]
];
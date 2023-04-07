<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_menu_pages
 */
return [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
        --div--;Plugin,
            pi_flexform,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridMenuPages,
            menu_item_config,'
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
                    ['Default cards', 'default-cards'],
                    ['Cards (custom settings)', 'cards'],
                    ['Columns (custom settings)', 'columns'],
                ],
                'default' => 'default-cards'
            ]
        ],
        'menu_item_config' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'item_column_width' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'media_column_width' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'row_justify' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'row_align' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'media_layout' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'container_width' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'container_offset' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'container_position' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'card_media_size' => [
            'displayCond' => 'FIELD:layout:=:cards',
        ],
        'text_column_width' => [
            'displayCond' => 'FIELD:layout:=:columns',
        ],
    ]
];
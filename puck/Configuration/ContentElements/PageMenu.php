<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
    'page_menu',
    label: 'Page menu',
    description: 'Plugin to display a menu of pages. Flexible layout options.',
    group: '03_menu',
    icon: 'page_menu',
    showItem: '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Advanced,
            pi_flexform,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridMenuPages,
            menu_item_config,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
                    ['Default cards', 'default-cards'],
                    ['Blog cards', 'blog-cards'],
                    ['Team cards', 'team-cards'],
                    ['Cards (custom settings)', 'cards'],
                    ['Columns (custom settings)', 'columns'],
                ]),
                'default' => 'default-cards'
            ]
        ],
        'flex_grow' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
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
    ],
    pluginName: 'PageMenu',
    flexForms: ['pi_flexform' => 'FILE:EXT:puck/Configuration/FlexForms/PageMenu.xml'],
);
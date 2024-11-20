<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
    'row_container',
    label: 'Row container',
    description: 'Container for text and media columns and cards. Flexible alignment and individual column widths.',
    icon: 'row_container',
    sorting: 80,
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridColumnsAlignment,
        --div--;Advanced,
            options,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
                    ['Rows', 'default', 'row_layout_row'],
                    ['Carousel', 'carousel', 'row_layout_carousel'],
                ]),
            ]
        ],
        'options' => [
            'label' => 'Carousel options',
            'displayCond' => 'FIELD:layout:IN:carousel',
        ]
    ],
    flexForms: [
        'options' => 'FILE:EXT:puck/Configuration/FlexForms/CarouselOptions.xml'
    ],
    containerConfiguration: [
        [
            ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_media_column, puck_card']]
        ]
    ],
);
<?php

return new \UBOS\Puck\ContentElementDefinition(
    'row',
    label: 'Row container',
    description: 'Container for text and media columns and cards. Flexible alignment and individual column widths.',
    icon: 'row',
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
            ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_child_column, puck_child_card']]
        ]
    ],
    dataProcessing: [
        [
            'processor' => 'B13\Container\DataProcessing\ContainerProcessor',
            'colPos' => 600,
            'as' => 'children_600',
        ]
    ]
);
<?php
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_row'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridColumnsAlignment,
        --div--;Advanced,
            options,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
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
    ]
];
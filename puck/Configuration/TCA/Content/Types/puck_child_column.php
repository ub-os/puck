<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_child_column
 */
return [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;childHeader,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainerWidth,
            --palette--;;gridMedia,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
        'item_column_width' => [
            'label' => 'Media item width'
        ]
    ]
];
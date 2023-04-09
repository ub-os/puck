<?php
use UBOS\Puck\Utility\TcaUtility;
use UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc;

$GLOBALS['TCA']['tt_content']['types']['puck_full_width_media'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            media_layout,    
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'media_layout' => [
            'config' => [
                'itemsProcFunc' => ContentItemsProcFunc::class . '->keepItems',
            ]
        ],
        'container_width' => [
            'displayCond' => 'FIELD:media_layout:IN:above,below',
        ],
        'container_offset' => [
            'displayCond' => 'FIELD:media_layout:IN:above,below',
        ],
        'container_position' => [
            'displayCond' => 'FIELD:media_layout:IN:above,below',
        ]
    ]
];
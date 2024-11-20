<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
    'cover_media',
    label: 'Cover media',
    description: 'Media element with 50% or 100% viewport width.',
    icon: 'cover_media',
    sorting: 20,
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            media_layout,    
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'media_layout' => [
            'config' => [
                'itemsProcFunc' => \UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc::class . '->keepItems',
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
        ],
        'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants()
    ]
);
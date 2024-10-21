<?php

use UBOS\Puck\ContentElementDefinition;
use UBOS\Puck\Utility\TcaUtility;
use UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc;

return new ContentElementDefinition(
    'modal',
    label: 'Modal',
    description: 'Modal desc new',
    icon: 'modal',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainerWidth,
            --palette--;;gridCard,
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
                'itemsProcFunc' => ContentItemsProcFunc::class . '->keepItems',
            ]
        ],
        'assets' => TcaUtility::configOverrideWithBreakpointCropVariants()
    ],
);
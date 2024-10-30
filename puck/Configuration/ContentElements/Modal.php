<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
    'modal',
    label: 'Modal',
    description: 'Hidden dialog that opens via hash link.',
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
                'itemsProcFunc' => \UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc::class . '->keepItems',
            ]
        ],
        'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants()
    ],
);
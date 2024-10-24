<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
    'media_column',
    label: 'Media column',
    description: 'Text and media child content element.',
    icon: 'media_column',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;childHeader,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainerWidth,
            --palette--;;gridMedia,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
        'item_column_width' => [
            'label' => 'Media item width'
        ],
        'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants()
    ]
);
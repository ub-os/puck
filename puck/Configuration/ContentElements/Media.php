<?php

return new \UBOS\Puck\ContentElementDefinition(
    'media',
    label: 'Text and media',
    description: 'Flexible text and media layouts.',
    icon: 'media',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
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
            'label' => 'Media item width',
        ],
        'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants()
    ]
);
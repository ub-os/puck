<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
    'container',
    label: 'Simple container',
    description: 'Container for accordions and other block items.',
    icon: 'container',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
    ],
    containerConfiguration: [
        [
            ['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_accordion']]
        ]
    ],
);
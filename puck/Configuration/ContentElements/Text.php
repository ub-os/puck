<?php

return new \UBOS\Puck\ContentElementDefinition(
    'text',
    label: 'Text',
    description: 'Simple text element.',
    icon: 'text',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ],
);
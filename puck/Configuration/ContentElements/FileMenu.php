<?php

return new \UBOS\Puck\ContentElementDefinition(
    'file_menu',
    label: 'File menu',
    description: 'Menu of downloadable files.',
    group: '03_menu',
    icon: 'file_menu',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
        --div--;Layout,
            --palette--;;gridContainer,
            item_column_width,
        --div--;Files,
            --palette--;;fileMenu,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ],
    pluginName: 'FileMenu',
);
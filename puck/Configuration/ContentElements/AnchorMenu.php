<?php

return new \UBOS\Puck\ContentElementDefinition(
    'anchor_menu',
    label: 'Anchor menu',
    description: 'Navigation of on-page anchors.',
    group: '03_menu',
    icon: 'anchor_menu',
    showItem: '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;headers,',
    columnsOverrides: [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ],
    pluginName: 'AnchorMenu',
);
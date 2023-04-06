<?php
use UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc;

$baseShowItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,';

$cropVariants = require __DIR__.'/../Common/CropVariants.php';

$types = [];

$types['puck_anchor'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header, subheader,'
        .$baseShowItem,
    'columnsOverrides' => [
        'header' => [
            'label' => 'Title'
        ],
        'subheader' => [
            'label' => 'URL Segment',
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['header'],
                    'fieldSeparator' => '-',
                    'replacements' => [
                        '/' => '',
                    ],
                ],
                'appearance' => [
                    'prefix' => 'UBOS\\Puck\\UserFunctions\\FormEngine\\SlugPrefix->getHash',
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInPid',
                'default' => '',
            ],
        ],
    ]
];

$types['puck_full_width_media'] = [
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
        .$baseShowItem,
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

$types['puck_media'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridMedia,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
        'assets' => [
            'displayCond' => 'FIELD:content_type:=:assets',
            'config' => [
                'overrideChildTca' => [
                    'columns' => [
                        'crop' => [
                        ],
                    ],
                ]
            ]
        ],
        'pages' => [
            'label' => 'Pages',
            'displayCond' => 'FIELD:content_type:=:page'
        ],
        'bodytext2' => [
            'label' => 'iFrame HTML',
            'config' => [
                'renderType' => 't3editor',
                'enableRichtext' => false,
            ],
            'displayCond' => 'FIELD:content_type:=:html'
        ],
        'media_layout' => [
            'onChange' => 'reload',
            'config' => [
            ]
        ],
        'content_type' => [
            'onChange' => 'reload',
            'config' => [
                'itemsProcFunc' => ContentItemsProcFunc::class . '->keepItems',
            ]
        ],
        'item_column_width' => [
            'label' => 'Media item width',
            'displayCond' => 'FIELD:content_type:=:assets',
        ],
        'row_justify' => [
            'displayCond' => 'FIELD:content_type:=:assets',

        ],
        'media_column_width' => [
        ],
        'text_column_width' => [
        ],
    ]
];

$types['puck_menu_anchors'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;headers,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];

$types['puck_menu_files'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
        --div--;Layout,
            --palette--;;gridContainer,
            item_column_width,
        --div--;Files,
            --palette--;;menu_files,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'assets' => [
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'assets',
                [
                ],
            )
        ],
    ]
];

$types['puck_menu_pages'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
        --div--;Plugin,
            pi_flexform,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridMenuPages,
            menu_item_config,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => [
                    ['Default cards', 'default-cards'],
                    ['Cards (custom settings)', 'cards'],
                    ['Columns (custom settings)', 'columns'],
                ],
                'default' => 'default-cards'
            ]
        ],
        'menu_item_config' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'item_column_width' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'media_column_width' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'row_justify' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'row_align' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'media_layout' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'container_width' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'container_offset' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'container_position' => [
            'displayCond' => 'FIELD:layout:IN:cards,columns',
        ],
        'card_media_size' => [
            'displayCond' => 'FIELD:layout:=:cards',
        ],
        'text_column_width' => [
            'displayCond' => 'FIELD:layout:=:columns',
        ],
    ]
];

$types['puck_menu_posts'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;appearanceLayout,
        --palette--;;headers,
        --div--;Plugin,
       pi_flexform,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => [
                    ['Blog cards', 'blog-cards'],
                ],
                'default' => 'blog-cards'
            ]
        ]
    ]
];

$types['puck_menu_persons'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
        --div--;Plugin,
            pi_flexform,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => [
                    ['Team cards', 'team-cards'],
                ],
                'default' => 'team-cards'
            ]
        ]
    ]
];

$types['puck_modal'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainerWidth,
            --palette--;;gridCard,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
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
    ]
];

$types['puck_hero'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;layout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
        'assets' => [
            'config' => [
                'overrideChildTca' => [
                    'columns' => [
                        'crop' => [
                            'config' => [
                                'cropVariants' => [
                                    '2:1' => $cropVariants['2:1'],
                                    '3:2' => $cropVariants['3:2'],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ]
];

$types['puck_hero_carousel'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;layout,
            --palette--;;headers,'
        .$baseShowItem,
    'columnsOverrides' => [
    ]
];

$types['puck_text'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];
$types['puck_container'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
    ]
];

$types['puck_row'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridColumns,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'layout' => [
            'config' => [
                'items' => [
                    ['Default', 'default'],
                    ['Carousel', 'carousel']
                ]
            ]
        ],
    ]
];

$types['puck_child_column'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;childHeader,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainerWidth,
            --palette--;;gridMedia,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ],
        ],
        'item_column_width' => [
            'label' => 'Media item width'
        ]
    ]
];

$types['puck_child_card'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;childHeader,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainerWidth,
            --palette--;;gridCard,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
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
    ]
];
$types['puck_child_accordion'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridMedia,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'item_column_width' => [
            'label' => 'Media item width'
        ]
    ]
];
$types['puck_child_hero_slide'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'assets' => [
            'config' => [
                'overrideChildTca' => [
                    'columns' => [
                        'crop' => [
                            'config' => [
                                'cropVariants' => [
                                    'default' => ['disabled' => true],
                                    'mobile' => ['disabled' => true],
                                    '2:1' => $cropVariants['2:1'],
                                    '3:2' => $cropVariants['3:2'],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ]
];

return $types;


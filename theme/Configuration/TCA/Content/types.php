<?php
use UBOS\Theme\UserFunctions\FormEngine\ContentItemsProcFunc;

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

$types['theme_accordions'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,        
        --div--;Items,
            inline_media,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ],
        'inline_media' => [
            'label' => 'Accordion items',
            'config' => [
                'overrideChildTca' => [
                    'types' => [
                        '1' => $GLOBALS['TCA']['tx_theme_domain_model_inline_media']['types']['accordions'],
                    ],
                ]
            ]
        ]
    ]
];

$types['theme_anchor'] = [
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
            'label' => '#',
        ]
    ]
];

$types['theme_cards'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,        
        --div--;Items,
            --palette--;;gridColumns,
            inline_media,'
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
        'inline_media' => [
            'label' => 'Cards items',
            'config' => [
                'overrideChildTca' => [
                    'types' => [
                        '1' => $GLOBALS['TCA']['tx_theme_domain_model_inline_media']['types']['cards'],
                    ]
                ]
            ]
        ]
    ]
];

$types['theme_columns'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,        
        --div--;Items,
            --palette--;;gridColumns,
            inline_media,'
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
        'inline_media' => [
            'label' => 'Columns items',
            'config' => [
                'overrideChildTca' => [
                    'types' => [
                        '1' => $GLOBALS['TCA']['tx_theme_domain_model_inline_media']['types']['columns'],
                    ]
                ]
            ]
        ]
    ]
];

$types['theme_full_width_media'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            media_layout,
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

$types['theme_media'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            --palette--;;gridMedia,
            assets,
            pages,
            bodytext2,
            content_type,'
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
            'label' => 'Default media item width',
            'displayCond' => 'FIELD:content_type:=:assets',
        ],
        'column_position' => [
            'displayCond' => 'FIELD:content_type:=:assets',

        ],
        'media_column_width' => [
        ],
        'text_column_width' => [
        ],
    ]
];

$types['theme_menu_anchors'] = [
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

$types['theme_menu_pages'] = [
    'showitem' => '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;appearance,
        --palette--;;headers,
        --palette--;;menu_pages,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];

$types['theme_modal'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainerWidth,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            --palette--;;gridCard,
            assets,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];

$types['theme_hero'] = [
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

$types['theme_hero_carousel'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;layout,
            --palette--;;headers,
        --div--;Items,
            inline_media,'
        .$baseShowItem,
    'columnsOverrides' => [
        'inline_media' => [
            'label' => 'Carousel items',
            'config' => [
                'overrideChildTca' => [
                    'types' => [
                        '1' => $GLOBALS['TCA']['tx_theme_domain_model_inline_media']['types']['hero_carousel'],
                    ],
                ]
            ]
        ]
    ]
];

$types['theme_text'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,'
        .$baseShowItem,
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];

return $types;


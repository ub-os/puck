<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_hero
 */
return [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;layout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,'
        .TcaUtility::getContentShowitemBase(),
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
                                'cropVariants' => TcaUtility::getCropVariants(['2:1','3:2']),
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ]
];
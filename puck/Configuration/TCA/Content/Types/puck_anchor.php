<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_anchor
 */
return [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header, subheader,'
        .TcaUtility::getContentShowitemBase(),
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
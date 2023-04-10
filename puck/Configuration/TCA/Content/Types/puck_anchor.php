<?php

use UBOS\Puck\UserFunctions\FormEngine\SlugPrefix;
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_anchor'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header, 
            subheader,'
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
                    'prefix' => SlugPrefix::class.'->getHash',
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInPid',
                'default' => '',
            ],
        ],
    ]
];
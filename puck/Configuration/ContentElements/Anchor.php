<?php

return new \UBOS\Puck\ContentElementDefinition(
    'anchor',
    label: 'Anchor',
    description: 'Anchor for anchor menu',
    group: '03_menu',
    icon: 'anchor',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header, 
            subheader,',
    columnsOverrides: [
        'header' => [
            'label' => 'Title'
        ],
        'subheader' => [
            'label' => 'URL Segment',
            'displayCond' => 'FIELD:CType:=:puck_anchor',
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
                    'prefix' => \UBOS\Puck\UserFunctions\FormEngine\SlugPrefix::class.'->getHash',
                ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInPid',
                'default' => '',
            ],
        ],
    ]
);
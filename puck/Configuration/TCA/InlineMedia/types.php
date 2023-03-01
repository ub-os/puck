<?php

$typesShowItemBase = '                
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
                    sys_language_uid, 
                    l10n_parent, 
                    l10n_diffsource, 
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
                    hidden';
$cropVariants = require __DIR__.'/../Common/CropVariants.php';
$overrideCropVariants = function($variantKeys) use (&$cropVariants): array {
    return [
        'config' => [
            'overrideChildTca' => [
                'columns' => [
                    'crop' => [
                        'config' => [
                            'cropVariants' => (array_intersect_key($cropVariants, array_flip(explode(',', $variantKeys)))),
                        ],
                    ]
                ]
            ]
        ]];
};

$types = [
    '1' => [
        'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
                    item_type,
                    header, 
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    --palette--;;gridMedia,
                    assets,
                '.$typesShowItemBase,
        'columnsOverrides' => [
            'assets' => $overrideCropVariants('default,mobile'),
        ]
    ],
    'accordions' => [
        'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
                    item_type,
                    header, 
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    --palette--;;gridMedia,
                    assets,
                '.$typesShowItemBase,
        'columnsOverrides' => [
            'assets' => $overrideCropVariants('default,mobile'),
        ]
    ],
    'hero_carousel' => [
        'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
                    item_type,
                    header, 
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    assets,
                '.$typesShowItemBase,
        'columnsOverrides' => [
            'assets' => $overrideCropVariants('2:1,3:2'),

        ]
    ],
    'columns' => [
        'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    item_type,
                    column_width,
                    responsive_order,
                    header,
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    --palette--;;gridMedia,
                    assets,
                '.$typesShowItemBase,
        'columnsOverrides' => [
            'assets' => $overrideCropVariants('default,mobile'),
        ]
    ],
    'cards' => [
        'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    item_type,
                    column_width,
                    header, 
                    bodytext, 
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
                    --palette--;;gridCard,
                    assets,
                '.$typesShowItemBase,
        'columnsOverrides' => [
            'assets' => $overrideCropVariants('default,mobile'),
        ]
    ],
];

return $types;
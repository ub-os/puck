$types['puck_model'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;gridContainer,
            --palette--;;appearanceLayout,
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
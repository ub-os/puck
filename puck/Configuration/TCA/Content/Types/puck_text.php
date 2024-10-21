<?php
use UBOS\Puck\Utility\TcaUtility;

$GLOBALS['TCA']['tt_content']['types']['puck_text'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
        'bodytext' => [
            'config' => [
                'enableRichtext' => true,
            ]
        ]
    ]
];
/*
ContentElementUtility::register(
    'text',
    group: '03_menu',
    icon: 'EXT:puck/Resources/Public/Icons/ContentElements/Text.svg',
    showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,',
    columnsOverrides: [
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'puck_text' => 'FILE:EXT:puck/Configuration/FlexForms/PageMenu.xml',
                ],
            ],
        ],
    ],
    pluginName: 'PageMenu',
    model: MenuPages::class,
    containerConfiguration: [],
    previewRenderer: 'UBOS\Puck\PreviewRenderer\TextPreviewRenderer',
);

(new ContentElement('text'))
    ->setShowItem('
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
        --palette--;;general,
        --palette--;;appearance,
        --palette--;;headers,
        --palette--;;bodytext,
    --div--;Layout,
        --palette--;;gridContainer,'
    )
    ->setColumnsOverrides([
        'pi_flexform' => [
            'config' => [
                'ds' => [
                    'puck_text' => 'FILE:EXT:puck/Configuration/FlexForms/PageMenu.xml',
                ],
            ],
        ],
    ])
    ->setPreviewRenderer('UBOS\Puck\PreviewRenderer\TextPreviewRenderer')
    ->setPlugin('PageMenu')
    ->setModel(MenuPages::class)
    ->setGroup('03_menu')
    ->setIcon('EXT:puck/Resources/Public/Icons/ContentElements/Text.svg')
    ->setContainerConfiguration([])
    ->register();
*/

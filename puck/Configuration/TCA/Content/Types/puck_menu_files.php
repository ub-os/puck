<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_menu_files
 */
return [
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
        .TcaUtility::getContentShowitemBase(),
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
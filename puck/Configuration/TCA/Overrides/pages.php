<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$cropVariants = require(ExtensionManagementUtility::extPath('puck') . 'Configuration/TCA/Common/CropVariants.php');

$GLOBALS['TCA']['pages']['columns']['teaser_text'] = [
    'label' => 'Teaser text',
    'config' => $GLOBALS['TCA']['pages']['columns']['abstract']['config'],
];
$GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    '3:2' => $cropVariants['3:2'],
    'social' => [
        'title' => '1.91:1',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '1.91:1',
                'value' => 1200/630
            ]
        ]
    ]
];
$GLOBALS['TCA']['pages']['columns']['og_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'];
$GLOBALS['TCA']['pages']['columns']['twitter_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'];

$GLOBALS['TCA']['pages']['columns']['icon'] = require ExtensionManagementUtility::extPath('puck') .'/Configuration/TCA/Common/Columns/Icon.php';

$GLOBALS['TCA']['pages']['palettes']['title']['showitem'] = '
    title,--linebreak--,slug,--linebreak--,nav_title,--linebreak--,subtitle,--linebreak--,teaser_text';
$GLOBALS['TCA']['pages']['palettes']['media']['showitem'] = '
    media,--linebreak--, icon';
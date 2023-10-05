<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\TcaUtility;


$cropVariants = TcaUtility::getCropVariants('3:2,16:9,1.91:1');

$GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $cropVariants;

$GLOBALS['TCA']['pages']['columns']['og_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $cropVariants;
$GLOBALS['TCA']['pages']['columns']['twitter_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $cropVariants;

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    'News folder',
    'news',
    'news_page',
];
$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    'Preset folder',
    'presets',
    'preset',
];

$GLOBALS['TCA']['pages']['columns']['icon'] = require ExtensionManagementUtility::extPath('puck') .'/Configuration/TCA/Common/Columns/Icon.php';

$GLOBALS['TCA']['pages']['columns']['teaser_text'] = [
    'label' => 'Teaser text',
    'config' => $GLOBALS['TCA']['pages']['columns']['abstract']['config'],
];

$GLOBALS['TCA']['pages']['columns']['post_date'] = [
    'label' => 'Date',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputDateTime',
        'size' => 16,
        'eval' => 'datetime',
    ],
];
$GLOBALS['TCA']['pages']['columns']['post_author'] = [
    'label' => 'Author',
    'config' => [
        'type' => 'group',
        'allowed' => 'tx_puck_domain_model_person',
        'size' => 1,
        'maxitems' => 1
    ],
];
$GLOBALS['TCA']['pages']['columns']['page_persons'] = [
    'label' => 'Person',
    'config' => [
        'required' => '1',
        'type' => 'group',
        'allowed' => 'tx_puck_domain_model_person',
        'foreign_table' => 'tx_puck_domain_model_person',
        'MM' => 'tx_puck_person_page_mm',
        'MM_opposite_field' => 'pages',
        'size' => 1,
        'maxitems' => 1
    ],
];

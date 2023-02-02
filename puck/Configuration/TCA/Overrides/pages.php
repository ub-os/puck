<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

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



// Post pages
$GLOBALS['TCA']['pages']['columns']['post_date'] = [
    'label' => 'Date',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputDateTime',
        'size' => 16,
        'eval' => 'datetime',
    ],
];
$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    'Post folder',
    'posts',
    'blog_post',
];
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-posts'] = 'post_folder';

$postDoktype = 60;
ExtensionManagementUtility::addTcaSelectItem(
    'pages',
    'doktype',
    [
        'Blog Post',
        $postDoktype,
        'blog_post'
    ],
    '1',
    'after'
);

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['pages'],
    [
        // add icon for new page type:
        'ctrl' => [
            'typeicon_classes' => [
                $postDoktype => 'blog_post',
                $postDoktype . '-hideinmenu' => "blog_post_hideinmenu",
            ],
        ],
        // add all page standard fields and tabs to your new page type
        'types' => [
            $postDoktype => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][\TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT]['showitem'],
                'columnsOverrides' => [
                    'post_date' => [
                        'config' => [
                            'required' => 1,
                        ]
                    ]
                ]
            ]
        ]
    ]
);
$GLOBALS['TCA']['pages']['palettes']['postTitle'] = [
    'label' => $GLOBALS['TCA']['pages']['palettes']['title']['label'],
    'showitem' => 'title,post_date,--linebreak--,slug,--linebreak--,nav_title,--linebreak--,subtitle,--linebreak--,teaser_text'
];
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;;postTitle',
    $postDoktype,
    'replace:--palette--;;title'
);
ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'php_tree_stop',
    254,
    'after:module'
);
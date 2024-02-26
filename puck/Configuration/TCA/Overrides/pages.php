<?php

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Domain\Repository\PageRepository;

require __DIR__ . '/../Pages/pages__columns.php';
require __DIR__ . '/../Pages/pages__palettes.php';

$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-news'] = 'news_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-presets'] = 'preset_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-categories'] = 'category_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-persons'] = 'person_folder';

ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA']['pages'],
    [
        // add all page standard fields and tabs to your new page type
        'types' => [
            PageRepository::DOKTYPES['news'] => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPES['default']]['showitem'],
                'columnsOverrides' => [
                    'post_date' => [
                        'config' => [
                            'required' => 1,
                        ]
                    ],
                    'url' => [
                        'config' => [
                            'required' => 0
                        ]
                    ],
                ]
            ],
            PageRepository::DOKTYPES['person'] => [
                'showitem' => $GLOBALS['TCA']['pages']['types'][PageRepository::DOKTYPES['default']]['showitem'],
            ]
        ]
    ]
);

function addToDoktypes($doktypes, $addition, $position): void
{
    ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        $addition,
        implode(',', array_map(function ($doktype) {
            return PageRepository::DOKTYPES[$doktype];
        }, $doktypes)),
        $position
    );
}

addToDoktypes(
    ['default', 'news'],
    '--div--;Teaser, --palette--;;teaser',
    'after:--palette--;;title'
);
addToDoktypes(
    ['shortcut'],
    '--div--;Teaser, --palette--;;teaser',
    'after:--palette--;;shortcutpage'
);
addToDoktypes(
    ['link'],
    '--div--;Teaser, --palette--;;teaser',
    'after:--palette--;;external'
);
addToDoktypes(
    ['shortcut', 'link'],
    '--palette--;;standard',
    'replace:doktype'
);
addToDoktypes(
    ['news'],
    'url;Redirect to URL, --palette--;;author',
    'after:--palette--;;title'
);
addToDoktypes(
    ['person'],
    'page_persons',
    'after:--palette--;;title'
);
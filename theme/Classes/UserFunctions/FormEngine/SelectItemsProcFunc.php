<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 *
 */
class SelectItemsProcFunc
{
    /**
     * @var array|array[]
     */
    public array $keepItemsMap = [
        'theme_text_media2' => [
            'layout' => ['cols5-5','cols6-4'],
            'imageorient' => [1,2,3,4,5,6],
            'content_type' => ['assets','page']
        ]
    ];

    /**
     * @param $params
     * @return void
     */
    public function keepItems(&$params): void
    {
        $keepItems = $this->keepItemsMap[$params['row']['CType'][0]][$params['field']];
        if ($keepItems) {
            $params['items'] = array_filter($params['items'], function ($item) use ($keepItems, $params) {
                return in_array($item[1], $keepItems);
            });
        }
    }

    /**
     * @param array $params
     * @return void
     */
    public function columnsInlineItemImageorient(&$params): void
    {
        $parentRow = BackendUtility::getRecord($params['inlineParentTableName'], $params['inlineParentUid'], '*', '', true);
        $items = [];
        if ($parentRow['imagecols'] < 3) {
            array_push($items,
            ['Right in text', 1,
                'imageorient_right-float'
            ],
            ['Left in text', 2,
                'imageorient_left-float'
            ],
            ['Right beside text', 3,
                'imageorient_right-top'
            ],
            ['Left beside text', 4,
                'imageorient_left-top'
            ],
            );
        }
        array_push($items,
            ['Above text', 5,
                'imageorient_top-center',
            ],
            ['Below text', 6,
                'imageorient_bottom-center',
            ],
            );
        if ($parentRow['frame_class'] != 'default') {
            if ($parentRow['imagecols'] < 2) {
                array_push($items,
                    ['Right beside text as cover image in card', 7,
                        'imageorient_right-cover-in-card'],
                    ['Left beside text as cover image in card', 8,
                        'imageorient_left-cover-in-card']
                );
            }
            array_push($items,
                ['Above text in card', 9,
                    'imageorient_above-in-card'],
                ['Below text in card', 10,
                    'imageorient_below-in-card'],
                ['Above text full-width in card', 11,
                    'imageorient_above-full-in-card'],
                ['Below text full-width in card', 12,
                    'imageorient_below-full-in-card'],
                );
        }
        $params['items'] = $items;

    }
}
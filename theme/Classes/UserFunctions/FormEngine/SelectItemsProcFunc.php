<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class SelectItemsProcFunc
{
    /**
     *
     * @param array $params
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
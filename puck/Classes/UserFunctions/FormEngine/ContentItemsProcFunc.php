<?php
namespace UBOS\Puck\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
class ContentItemsProcFunc extends BaseItemsProcFunc
{
    /**
     * @var array|array[]
     */
    protected array $keepItemsMap = [
        'puck_media' => [
            'content_type' => 'assets,html'
        ],
        'puck_full_width_media' => [
            'media_layout' => 'above,below,left,right'
        ],
        'puck_modal' => [
            'media_layout' => 'above,below,left,right'
        ],
        'puck_menu_pages' => [
            'media_layout' => 'above,below,left,right'
        ]
    ];

    /**
     * @param $params
     * @return void
     */
    public function keepItems(&$params): void
    {
        $keepItems = $this->keepItemsMap[$this->val($params['row']['CType'])][$params['field']];
        if ($keepItems) {
            $params['items'] = $this->filterItemsByValues($params['items'], $keepItems);
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function containerOffset(&$params): void
    {
        // only allow offset values that are smaller than half of (12 - container width)
        $items = array_filter($params['items'], function ($item) use ($params) {
            return $item[1] <= ((12 - $this->val($params['row']['container_width'])) / 2);
        });
        $params['items'] = $items;
    }

    /**
     * @param $params
     * @return void
     */
    public function mediaLayout(&$params): void
    {
        $CType = $this->val($params['row']['CType']);
        if ($CType != 'puck_menu_pages') {
            return;
        }
        $allowedValues = 'above,below,left,right';
        $itemWidth = $this->val($params['row']['item_column_width']);
        if ($itemWidth < 4) {
            $allowedValues = 'above,below';
        }
        $params['items'] = $this->filterItemsByValues($params['items'], $allowedValues);
    }

    /**
     * @param $params
     * @return void
     */
    public function itemColumnWidth(&$params): void
    {
        // maximum width = media element with media_layout beside/float ? media width : container width
        if ($this->val($params['row']['CType']) === 'puck_media' && in_array($this->val($params['row']['media_layout']), ['left','right','left-float','right-float'])) {
            $maximum = $this->val($params['row']['media_column_width']);
        } else {
            $maximum = $this->val($params['row']['container_width']);
        }
        $items = array_filter($params['items'], function ($item) use ($params, $maximum) {
            return $item[1] <= $maximum;
        });
        $params['items'] = $items;
    }

    /**
     * @param $params
     * @return void
     */
    public function textColumnWidth(&$params): void
    {
        $CType = $this->val($params['row']['CType']);
        $mediaLayout = $this->val($params['row']['media_layout']);
        $containerWidth = $this->val($params['row']['container_width']);
        if ($CType == 'puck_menu_pages') {
            $containerWidth = $this->val($params['row']['item_column_width']);
        }
        // maximum width = container width - media width
        if (in_array($CType, ['puck_media','puck_menu_pages']) && in_array($mediaLayout, ['left-float','right-float','above','below'])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($containerWidth) {
                return $item[1] == $containerWidth;
            });
            return;
        }
        $maximum = $containerWidth - $this->val($params['row']['media_column_width']);
        $items = array_filter($params['items'], function ($item) use ($params, $maximum) {
            return $item[1] <= $maximum;
        });
        $params['items'] = $items;
    }

    /**
     * @param $params
     * @return void
     */
    public function mediaColumnWidth(&$params): void
    {
        $CType = $this->val($params['row']['CType']);
        // maximum width = container width - media width
        if (!in_array($CType, ['puck_media','puck_modal','puck_menu_pages'])) {
            return;
        }
        $mediaLayout = $this->val($params['row']['media_layout']);
        $containerWidth = $this->val($params['row']['container_width']);
        if ($CType == 'puck_menu_pages') {
            $containerWidth = $this->val($params['row']['item_column_width']);
        }
        if (in_array($mediaLayout, ['above','below'])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($containerWidth) {
                return $item[1] == $containerWidth;
            });
            return;
        }
        if (in_array($mediaLayout, ['left-float','right-float']) || $CType !== 'puck_media' ) {
            // if media_layout is float: maximum width = container width - 2
            $maximum = $containerWidth - 2;
        } else {
            // maximum width = container width - text width
            $maximum = $containerWidth - $this->val($params['row']['text_column_width']);
        }
        $items = array_filter($params['items'], function ($item) use ($params, $maximum) {
            return $item[1] <= $maximum;
        });
        $params['items'] = $items;
    }
}

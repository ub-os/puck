<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 *
 */
class InlineMediaItemsProcFunc extends BaseItemsProcFunc
{
    
    /**
     * @param $params
     * @return void
     */
    public function imageOrient(&$params): void
    {
        $parentRow = $this->getInlineParentRow($params);
        // if parent isn't a columns or carousel element, do nothing
        if ($parentRow['CType'] !== 'theme_columns' && $parentRow['CType'] !== 'theme_carousel') {
            return;
        }
        // if column width is smaller than 4 allow only imageorient "image above" and "image below"
        if (($this->val($params['row']['column_width']) == 0 && $this->val($parentRow['item_column_width']) < 4) || $this->val($params['row']['column_width']) < 4) {
            $items = array_filter($params['items'], function ($item) {
                return $item[1] > 4;
            });
            $params['items'] = $items;
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function columnWidth(&$params): void
    {
        // maximum width = container width of parent content element
        $maximum = $this->getInlineParentRow($params)['container_width'];
        $items = array_filter($params['items'], function ($item) use ($maximum) {
            return $item[1] <= $maximum;
        });
        $params['items'] = $items;
    }

    /**
     * @param $params
     * @return void
     */
    public function itemColumnWidth(&$params): void
    {
        $columnWidth = $this->getComputedContainerWidth($params);
        if ($this->val($params['row']['imageorient']) < 5) {
            $maximum = $this->val($params['row']['media_column_width']);
        } else {
            $maximum = $columnWidth;
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
        $columnWidth = $this->getComputedContainerWidth($params);
        if (in_array($this->val($params['row']['imageorient']), [1,2,5,6])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($columnWidth) {
                return $item[1] === $columnWidth;
            });
            return;
        }
        // maximum width = column width - media width
        $maximum = $columnWidth - $this->val($params['row']['media_column_width']);
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
        $columnWidth = $this->getComputedContainerWidth($params);
        if ($this->val($params['row']['imageorient']) >= 5) {
            $params['items'] = array_filter($params['items'], function ($item) use ($columnWidth) {
                return $item[1] === $columnWidth;
            });
            return;
        }
        if ($this->val($params['row']['imageorient']) < 3) {
            // if imageorient is float: maximum width = column width - 2
            $maximum = $columnWidth - 2;
        } else {
            // maximum width = container width - text width
            $maximum = $columnWidth - $this->val($params['row']['text_column_width']);
        }
        $items = array_filter($params['items'], function ($item) use ($params, $maximum) {
            return $item[1] <= $maximum;
        });
        $params['items'] = $items;
    }

    protected function getComputedContainerWidth($params) : int
    {
        $parentRow = $this->getInlineParentRow($params);
        // if parent is a content element with columns use column width or parent default fallback, else use parent container width
        if ($parentRow['CType'] === 'theme_columns' || $parentRow['CType'] === 'theme_carousel') {
            return $this->val($params['row']['column_width']) ? : $this->getInlineParentRow($params)['item_column_width'];
        } else {
            return $this->getInlineParentRow($params)['container_width'];
        }
    }
}

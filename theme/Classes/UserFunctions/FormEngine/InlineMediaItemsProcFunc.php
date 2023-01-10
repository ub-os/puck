<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 *
 */
class InlineMediaItemsProcFunc extends BaseItemsProcFunc
{

    /**
     * @param array $params
     * @return ?array
     */
    protected function getInlineParentRow(array $params): ?array
    {
        return BackendUtility::getRecord($params['inlineParentTableName'], $params['inlineParentUid'], '*', '', true) ?? null;
    }

    /**
     * @param $params
     * @return void
     */
    public function itemType(&$params): void
    {
        $parentRow = $this->getInlineParentRow($params);
        if (!$parentRow) {
            return;
        }
        $ctype = $parentRow['CType'];
        $items = array_filter($params['items'], function ($item) use ($ctype) {
            return $item[1] == str_replace('theme_', '', $ctype);
        });
        $params['items'] = $items;
    }

    /**
     * @param $params
     * @return void
     */
    public function media_layout(&$params): void
    {
        $parentRow = $this->getInlineParentRow($params);
        if (!$parentRow) {
            return;
        }
        // if parent isn't a card/columns/carousel element, do nothing
        if (!GeneralUtility::inList('theme_columns,theme_cards', $this->val($parentRow['CType']))) {
            return;
        }
        $items = $params['items'];
        if (GeneralUtility::inList('theme_cards', $this->val($parentRow['CType']))) {
            $items = array_filter($items, function ($item) {
                return in_array($item[1], ['above','below','left','right']);
            });
        }
        // if column width is smaller than 4 allow only media_layout "image above" and "image below"
        if (($this->val($params['row']['column_width']) == 0 && $this->val($parentRow['item_column_width']) < 4)
            || ($this->val($params['row']['column_width']) < 4  && $this->val($params['row']['column_width']) != 0)) {
            $items = array_filter($items, function ($item) {
                return in_array($item[1], ['above','below']);
            });
        }
        $params['items'] = $items;
    }

    /**
     * @param $params
     * @return void
     */
    public function columnWidth(&$params): void
    {
        $parentRow = $this->getInlineParentRow($params);
        if (!$parentRow) {
            return;
        }
        // maximum width = container width of parent content element
        $maximum = $parentRow['container_width'];
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
        if (in_array($this->val($params['row']['media_layout']), ['left','right','left-float','right-float'])) {
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
        if (in_array($this->val($params['row']['media_layout']), ['above','below','left-float','right-float'])) {
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
        $parentRow = $this->getInlineParentRow($params);
        if (!$parentRow) {
            return;
        }
        $columnWidth = $this->getComputedContainerWidth($params);
        if (GeneralUtility::inList('theme_cards', $this->val($parentRow['CType'])) && in_array($this->val($params['row']['media_layout']), ['left','right','left-float','right-float'])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($columnWidth) {
                return $item[1] <= ($columnWidth - 2);
            });
            return;
        }
        if (in_array($this->val($params['row']['media_layout']), ['above','below'])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($columnWidth) {
                return $item[1] === $columnWidth;
            });
            return;
        }
        if (in_array($this->val($params['row']['media_layout']), ['left-float','right-float'])) {
            // if media_layout is float: maximum width = column width - 2
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
        if (!$parentRow) {
            return 12;
        }
        // if parent is a content element with columns use column width or parent default fallback, else use parent container width
        if (GeneralUtility::inList('theme_columns,theme_cards', $this->val($parentRow['CType']))) {
            return $this->val($params['row']['column_width']) ? : $this->val($this->getInlineParentRow($params)['item_column_width']);
        } else {
            return $this->val($this->getInlineParentRow($params)['container_width']);
        }
    }
}

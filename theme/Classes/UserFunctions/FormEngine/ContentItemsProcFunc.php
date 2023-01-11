<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
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
        'theme_media' => [
            'content_type' => ['assets','html']
        ],
        'theme_full_width_media' => [
            'media_layout' => ['above','below','left','right']
        ],
        'theme_modal' => [
            'media_layout' => ['above','below','left','right']
        ],
    ];

    /**
     * @param $params
     * @return void
     */
    public function keepItems(&$params): void
    {
        $keepItems = $this->keepItemsMap[$this->val($params['row']['CType'])][$params['field']];
        if ($keepItems) {
            $params['items'] = array_filter($params['items'], function ($item) use ($keepItems) {
                return in_array($item[1], $keepItems);
            });
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
    public function itemColumnWidth(&$params): void
    {
        // maximum width = media element with media_layout beside/float ? media width : container width
        if ($this->val($params['row']['CType']) === 'theme_media' && in_array($this->val($params['row']['media_layout']), ['left','right','left-float','right-float'])) {
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
        // maximum width = container width - media width
        if ($this->val($params['row']['CType']) === 'theme_media' && in_array($this->val($params['row']['media_layout']), ['left-float','right-float','above','below'])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($params) {
                return $item[1] == $this->val($params['row']['container_width']);
            });
            return;
        }
        $maximum = $this->val($params['row']['container_width']) - $this->val($params['row']['media_column_width']);
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
        // maximum width = container width - media width
        if (!in_array($this->val($params['row']['CType']), ['theme_media','theme_modal'])) {
            return;
        }
        if (in_array($this->val($params['row']['media_layout']), ['above','below'])) {
            $params['items'] = array_filter($params['items'], function ($item) use ($params) {
                return $item[1] == $this->val($params['row']['container_width']);
            });
            return;
        }
        if (in_array($this->val($params['row']['media_layout']), ['left-float','right-float']) || $this->val($params['row']['CType']) === 'theme_modal') {
            // if media_layout is float: maximum width = container width - 2
            $maximum = $this->val($params['row']['container_width']) - 2;
        } else {
            // maximum width = container width - text width
            $maximum = $this->val($params['row']['container_width']) - $this->val($params['row']['text_column_width']);
        }
        $items = array_filter($params['items'], function ($item) use ($params, $maximum) {
            return $item[1] <= $maximum;
        });
        $params['items'] = $items;
    }

}

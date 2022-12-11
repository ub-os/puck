<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


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
        // maximum width = media element with imageorient beside/float ? media width : container width
        if ($this->val($params['row']['CType']) === 'theme_media' && $this->val($params['row']['imageorient']) < 5) {
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
        if ($this->val($params['row']['CType']) === 'theme_media' && in_array($this->val($params['row']['imageorient']), [1,2,5,6])) {
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
        if ($this->val($params['row']['CType']) === 'theme_media' && $this->val($params['row']['imageorient']) >= 5) {
            $params['items'] = array_filter($params['items'], function ($item) use ($params) {
                return $item[1] == $this->val($params['row']['container_width']);
            });
            return;
        }
        if ($this->val($params['row']['imageorient']) < 3) {
            // if imageorient is float: maximum width = container width - 2
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

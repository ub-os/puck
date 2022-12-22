<?php
namespace UBOS\Theme\UserFunctions\FormEngine;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 *
 */
class BaseItemsProcFunc
{

    /**
     * @var array|array[]
     */
    protected array $keepItemsMap = [];

    /**
     * @param $params
     * @return void
     */
    protected function keepItems(&$params): void
    {
        $keepItems = $this->keepItemsMap[$params['field']];
        if ($keepItems) {
            $params['items'] = array_filter($params['items'], function ($item) use ($keepItems) {
                return in_array($item[1], $keepItems);
            });
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    // workaround for issue: sometimes field values ($params['row'][$fieldName]) are nested as the first element of an array
    protected function getValueFromArrayOrValue($value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (isset($value[0])) {
            return $value[0];
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    // shorthand for getValueFromArrayOrValue
    protected function val($value): mixed
    {
        return $this->getValueFromArrayOrValue($value);
    }

}

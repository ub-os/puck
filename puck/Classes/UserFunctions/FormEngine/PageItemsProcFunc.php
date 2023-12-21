<?php
namespace UBOS\Puck\UserFunctions\FormEngine;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Puck\Domain\Repository\PageRepository;
use UBOS\Puck\UserFunctions\FormEngine\BaseItemsProcFunc;

class PageItemsProcFunc extends BaseItemsProcFunc
{
    protected array $keepItemsMap = [
    ];

    public function doktype(&$params): void
    {
        $pidRow = BackendUtility::getRecord('pages', $params['row']['pid']);
        if ($pidRow['module'] == 'news') {
            $params['items'] = $this->filterItemsByValues($params['items'], (string)PageRepository::DOKTYPES['news']);
        }
    }

    public function backendLayout(&$params): void
    {
        $allowedList = $params['TSconfig']['doktypes.'][$this->val($params['row']['doktype'])] ?? '';
        $allowedList = GeneralUtility::trimExplode(',', $allowedList, true);
        foreach($allowedList as $key) {
            $params['items'][] = [
                'label' => $key,
                'value' => 'pagets__' . $key
            ];}
    }
}

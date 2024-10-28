<?php

namespace UBOS\Puck\Preview;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use UBOS\Puck\UserFunctions\FormEngine\ContentItemsProcFunc;

class PageMenuPreviewRenderer extends BasicPreviewRenderer
{

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();
        $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
        $settings = $flexformService->convertFlexFormContentToArray($record['pi_flexform'])['settings'];
        $this->view->assign('pageMenuSettings', $settings);
        $this->view->assign('pageMenuProcessed', $this->getDataForPageMenuFlexFormPreview($settings));
        $item->setRecord($record);
        return parent::renderPageModulePreviewContent($item);
    }

    protected function getDataForPageMenuFlexFormPreview($settings): array
    {
        $processedMenuData = [];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        foreach(['records', 'parents'] as $key) {
            if (isset($settings['demand'][$key]) && !empty($settings['demand'][$key])) {
                $uids = explode(',', $settings['demand'][$key]);
                foreach ($uids as $uid) {
                    $page = BackendUtility::getRecord('pages', $uid, '*', '', true);
                    if (!$page) {
                        continue;
                    }
                    $page['backend_link'] = $uriBuilder->buildUriFromRoute(
                        'web_layout',
                        ['id' => $page['uid']]
                    );
                    $page['table'] = 'pages';
                    $page['backend_link_title'] = 'Switch to this page';
                    $processedMenuData[$key][] = $page;
                }
            }
        }
        $doktypes = ['row' => ['uid' => 1], 'items' => []];
        (new ContentItemsProcFunc())->doktypes($doktypes);
        $processedMenuData['doktypeIcons'] = [];
        foreach($doktypes['items'] as $item) {
            if (GeneralUtility::inList($settings['demand']['types'] ?? '', $item['value'])) {
                $processedMenuData['doktypeIcons'][$item['label']] = $item['icon'];
            }
        }
        $recordGroups = [
            'author' => [
                'table' => 'tx_puck_domain_model_person',
                'title' => 'Author',
                'titleField' => 'name',
                'uids' => $settings['demand']['authors'] ?? ''
            ],
            'categories' => [
                'table' => 'sys_category',
                'title' => 'Categories',
                'titleField' => 'title',
                'uids' => $settings['demand']['categories']['0']['uids'] ?? ''
            ],
            'filter_categories' => [
                'table' => 'sys_category',
                'title' => 'Filter categories',
                'titleField' => 'title',
                'uids' => isset($settings['categoryFilter']) ? $settings['categoryFilter']['categories'] : ''
            ],
            'filter_category_groups' => [
                'table' => 'sys_category',
                'title' => 'Filter category groups',
                'titleField' => 'title',
                'uids' => isset($settings['categoryFilter']) ? $settings['categoryFilter']['treeCategories'] : ''
            ],
        ];
        foreach($recordGroups as $key => $recordGroup) {
            if (isset($recordGroup['uids']) && !empty($recordGroup['uids'])) {
                $uids = explode(',', $recordGroup['uids']);
                foreach ($uids as $uid) {
                    $record = BackendUtility::getRecord($recordGroup['table'], $uid, '*', '', true);
                    $record['backend_link'] = $uriBuilder->buildUriFromRoute(
                        'record_edit',
                        [
                            'edit' => [$recordGroup['table'] => [$uid => 'edit']],
                            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                        ]
                    );
                    $record['table'] = $recordGroup['table'];
                    if ($recordGroup['titleField'] !== 'title' && isset($record[$recordGroup['titleField']])) {
                        $record['title'] = $record[$recordGroup['titleField']];
                    }
                    $record['backend_link_title'] = 'Edit record';
                    $processedMenuData[$key][] = $record;
                }
            }
        }
        return $processedMenuData;
    }

}
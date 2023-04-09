<?php

namespace UBOS\Puck\Preview;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use B13\Container\Backend\Preview\ContainerPreviewRenderer;
use B13\Container\Tca\Registry;

class PuckPreviewRenderer implements PreviewRendererInterface
{

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:puck/Resources/Private/Fluid/Backend/Templates/ContentPreview/Header.html'));
        $view->setPartialRootPaths(['EXT:puck/Resources/Private/Fluid/Backend/Partials/ContentPreview/']);
        $view->assign('item', $item);

        return $view->render();
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        // check if the record is a container element, if so, render the container preview
        $containerPreview = '';
        $registry = GeneralUtility::makeInstance(Registry::class);
        if ($registry->isContainerElement($record['CType'])) {
            $containerPreviewRenderer = GeneralUtility::makeInstance(ContainerPreviewRenderer::class);
            $containerPreview = $containerPreviewRenderer->renderPageModulePreviewContent($item);
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:puck/Resources/Private/Fluid/Backend/Templates/ContentPreview/Content.html'));
        $view->setPartialRootPaths(['EXT:puck/Resources/Private/Fluid/Backend/Partials/ContentPreview/']);

        // add flexform data to the record
        if ($record['pi_flexform']) {
            $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
            $flexform = $flexformService->convertFlexFormContentToArray($record['pi_flexform']);
            $record['pi_flexform'] = $flexform;
            $view->assign('processedMenuData', $this->getDataForPageMenuFlexFormPreview($flexform));
        }
        $item->setRecord($record);
        $view->assign('item', $item);

        return $view->render() . $containerPreview;
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:puck/Resources/Private/Fluid/Backend/Templates/ContentPreview/Footer.html'));
        $view->setPartialRootPaths(['EXT:puck/Resources/Private/Fluid/Backend/Partials/ContentPreview/']);
        $view->assign('item', $item);

        return $view->render();
    }

    public function wrapPageModulePreview(string $header, string $content, GridColumnItem $item): string
    {
        return $header . $content;
    }

    protected function getDataForPageMenuFlexFormPreview($flexform): array
    {
        $processedMenuData = [];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        foreach(['pages', 'parents'] as $key) {
            if (isset($flexform['settings'][$key]) && !empty($flexform['settings'][$key])) {
                $uids = explode(',', $flexform['settings'][$key]);
                foreach ($uids as $uid) {
                    $page = BackendUtility::getRecord('pages', $uid, 'title,uid', '', true);
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
        $recordGroups = [
          'author' => [
            'table' => 'tx_puck_domain_model_person',
            'title' => 'Author',
            'titleField' => 'name',
              'uids' => $flexform['settings']['demand']['author']
          ],
            'categories' => [
                'table' => 'sys_category',
                'title' => 'Categories',
                'titleField' => 'title',
                'uids' => $flexform['settings']['demand']['category']['list']
            ],
            'filter_categories' => [
                'table' => 'sys_category',
                'title' => 'Categories in filter',
                'titleField' => 'title',
                'uids' => $flexform['settings']['template']['filterCategories']
            ],
        ];
        foreach($recordGroups as $key => $recordGroup) {
            if (isset($recordGroup['uids']) && !empty($recordGroup['uids'])) {
                $uids = explode(',', $recordGroup['uids']);
                foreach ($uids as $uid) {
                    $record = BackendUtility::getRecord($recordGroup['table'], $uid, 'uid,' . $recordGroup['titleField'], '', true);
                    $record['backend_link'] = $uriBuilder->buildUriFromRoute(
                        'record_edit',
                        [
                            'edit' => [$recordGroup['table'] => [$uid => 'edit']],
                            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                        ]
                    );
                    $record['table'] = $recordGroup['table'];
                    $record['title'] = $record[$recordGroup['titleField']];
                    $record['backend_link_title'] = 'Edit record';
                    $processedMenuData[$key][] = $record;
                }
            }
        }
        return $processedMenuData;
    }

}
<?php

namespace UBOS\Puck\Preview;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;

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

        // add flexform data to the record
        if ($record['pi_flexform']) {
            $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
            $flexform = $flexformService->convertFlexFormContentToArray($record['pi_flexform']);
            $record['pi_flexform'] = $flexform;
        }
        $item->setRecord($record);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:puck/Resources/Private/Fluid/Backend/Templates/ContentPreview/Content.html'));
        $view->setPartialRootPaths(['EXT:puck/Resources/Private/Fluid/Backend/Partials/ContentPreview/']);
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

}
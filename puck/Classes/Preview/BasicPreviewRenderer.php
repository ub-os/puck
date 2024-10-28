<?php

namespace UBOS\Puck\Preview;


use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use B13\Container\Backend\Preview\ContainerPreviewRenderer;
use B13\Container\Tca\Registry;

class BasicPreviewRenderer implements PreviewRendererInterface
{
    protected ViewInterface $view;
    protected RecordFactory $recordFactory;
    public function __construct()
    {
        $this->view = GeneralUtility::makeInstance(ViewFactoryInterface::class)->create(
            new ViewFactoryData(
                templateRootPaths: ['EXT:puck/Resources/Private/Fluid/Backend/Templates/ContentPreview/'],
                partialRootPaths: ['EXT:puck/Resources/Private/Fluid/Backend/Partials/ContentPreview/'],
            )
        );
        $this->recordFactory = GeneralUtility::makeInstance(RecordFactory::class);
    }

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        $this->view->assign('item', $item);
        $this->view->assign('record', $this->recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $item->getRecord()));
        $this->view->assign('editLink', $this->getEditLink($item->getRecord()));
        return $this->view->render('Header');
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();

        // check if the record is a container element, if so, render the container preview
        $containerPreview = '';
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        if ($containerRegistry->isContainerElement($record['CType'])) {
            $containerPreviewRenderer = GeneralUtility::makeInstance(ContainerPreviewRenderer::class);
            $containerPreview = $containerPreviewRenderer->renderPageModulePreviewContent($item);
        }

        $item->setRecord($record);
        $this->view->assign('item', $item);
        $this->view->assign('record', $this->recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $item->getRecord()));
        $this->view->assign('editLink', $this->getEditLink($record));

        return $this->view->render('Content') . $containerPreview;
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        $this->view->assign('item', $item);
        $this->view->assign('record', $this->recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $item->getRecord()));
        return $this->view->render('Footer');
    }

    public function wrapPageModulePreview(string $header, string $content, GridColumnItem $item): string
    {
        return $header . $content;
    }

    protected function getEditLink($record): string
    {
        return GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('record_edit', [
            'edit' => [
                'tt_content' => [
                    $record['uid'] => 'edit'
                ]
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        ]);
    }
}
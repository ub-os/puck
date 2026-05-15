<?php

namespace UBOS\Puck\Backend;


use B13\Container\Backend\Preview\GridRenderer;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\Preview\RecordFieldPreviewProcessor;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;

/**
 * Default preview renderer for content elements.
 */
class BasicPreviewRenderer implements PreviewRendererInterface
{
	protected ViewInterface $view;

	public function __construct(
		protected RecordFactory $recordFactory,
		protected FrontendInterface $runtimeCache,
		protected GridRenderer $gridRenderer,
		protected RecordFieldPreviewProcessor $fieldProcessor,
	)
	{
		$this->view = GeneralUtility::makeInstance(ViewFactoryInterface::class)->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Fluid/Backend/Templates/ContentPreview/'],
				partialRootPaths: ['EXT:puck/Resources/Private/Fluid/Backend/Partials/ContentPreview/'],
			)
		);
	}

	public function renderPageModulePreviewHeader(GridColumnItem $item): string
	{
		$this->runtimeCache->set('tx_container_current_gridColumItem', $item);
		$record = $item->getRecord();
		$request = $item->getContext()->getCurrentRequest();
		$this->view->assign('item', $item);
		$this->view->assign('record', $record);
		return $this->fieldProcessor->linkToEditForm(
			$this->view->render('Header'),
			$record,
			$request
		);
	}

	public function renderContainerGridPreview(GridColumnItem $item): string
	{
		// check if the record is a container element, if so, render the container preview
		$containerRegistry = GeneralUtility::makeInstance(Registry::class);
		$preview = '';
		$record = $item->getRecord();
		if ($containerRegistry->isContainerElement($record->get('CType'))) {
			$preview = $this->gridRenderer->renderGrid($record->toArray(), $item->getContext());
		}
		return $preview;
	}

	public function renderPageModulePreviewContent(GridColumnItem $item): string
	{
		$record = $item->getRecord();
		$request = $item->getContext()->getCurrentRequest();
		$thumbnailHtml = '';
		foreach (['media', 'image', 'assets'] as $fieldName) {
			if ($record->has($fieldName) && $record->get($fieldName)) {
				$thumbnailHtml .= $this->fieldProcessor->prepareFiles($record->get($fieldName));
			}
		}
		$bodyTextHtml = $this->fieldProcessor->prepareText($record, 'bodytext') ?? '';

		$this->view->assign('item', $item);
		$this->view->assign('record', $record);
		$this->view->assign('txContainerGrid', $this->renderContainerGridPreview($item));
		if ($bodyTextHtml || $thumbnailHtml) {
			$this->view->assign('linkedBody',
				$this->fieldProcessor->linkToEditForm(
					'<div style="display:flex;flex-direction:column;gap:1em">' . $bodyTextHtml . $thumbnailHtml . '</div>',
					$record,
					$request
				)
			);
		}
		return $this->view->render('Content');
	}

	public function renderPageModulePreviewFooter(GridColumnItem $item): string
	{
		$record = $item->getRecord();
		$footerColumns = [
			'layout', 'frame_class', 'container_width', 'container_position', 'container_offset', 'media_layout',
		];
		$fieldsWithLabels = [];
		foreach ($footerColumns as $fieldName) {
			if (!$record->has($fieldName) || !$record->get($fieldName) || $record->get($fieldName) === $GLOBALS['TCA']['tt_content']['columns'][$fieldName]['config']['default'] ?? '') {
				continue;
			}
			$processed = $this->fieldProcessor->prepareFieldWithLabel($record, $fieldName);
			$fieldsWithLabels[$fieldName] = $processed;
		}
		$this->view->assign('item', $item);
		$this->view->assign('record', $record);
		$this->view->assign('fieldsWithLabels', $fieldsWithLabels);
		return $this->view->render('Footer');
	}

	public function wrapPageModulePreview(string $header, string $content, GridColumnItem $item): string
	{
		return $header . $content;
	}
}
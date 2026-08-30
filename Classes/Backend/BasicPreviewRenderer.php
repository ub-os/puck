<?php

declare(strict_types=1);

namespace UBOS\Puck\Backend;

use B13\Container\Backend\Preview\GridRenderer;
use B13\Container\Tca\Registry;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\Preview\RecordFieldPreviewProcessor;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
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
	public function __construct(
		protected readonly ViewFactoryInterface $viewFactory,
		protected readonly RecordFactory $recordFactory,
		protected readonly GridRenderer $gridRenderer,
		protected readonly RecordFieldPreviewProcessor $fieldProcessor,
	) {}

	/**
	 * Creates a fresh, request-aware view for a single preview render.
	 */
	protected function createView(?ServerRequestInterface $request): ViewInterface
	{
		return $this->viewFactory->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Templates/Backend/ContentPreview/'],
				partialRootPaths: ['EXT:puck/Resources/Private/Templates/Backend/Partials/ContentPreview/'],
				request: $request,
			)
		);
	}

	public function renderPageModulePreviewHeader(GridColumnItem $item): string
	{
		$record = $item->getRecord();
		$request = $item->getContext()->getCurrentRequest();
		$view = $this->createView($request);
		$view->assign('item', $item);
		$view->assign('record', $record);
		return $this->fieldProcessor->linkToEditForm(
			$view->render('Header'),
			$record,
			$request
		);
	}

	public function renderContainerGridPreview(GridColumnItem $item): string
	{
		// check if the record is a container element, if so, render the container preview
		$containerRegistry = GeneralUtility::makeInstance(Registry::class);
		$record = $item->getRecord();
		if (!$containerRegistry->isContainerElement($record->get('CType'))) {
			return '';
		}
		return $this->gridRenderer->renderGrid($record->toArray(), $item->getContext());
	}

	public function renderPageModulePreviewContent(GridColumnItem $item): string
	{
		$record = $item->getRecord();
		$request = $item->getContext()->getCurrentRequest();
		$thumbnailHtml = '';
		$bodyTextHtml = '';
		foreach (['media', 'image', 'assets'] as $fieldName) {
			if ($record->has($fieldName) && $record->get($fieldName)) {
				$thumbnailHtml .= $this->fieldProcessor->prepareFiles($record->get($fieldName));
			}
		}
		if ($record->has('bodytext') && $record->get('bodytext')) {
			$bodyTextHtml = $this->fieldProcessor->prepareText($record, 'bodytext') ?? '';
		}
		$view = $this->createView($request);
		$view->assign('item', $item);
		$view->assign('record', $record);
		$view->assign('txContainerGrid', $this->renderContainerGridPreview($item));
		$view->assign(
			'linkedBody',
			$this->fieldProcessor->linkToEditForm(
				'<div class="puck-preview-body">' . $bodyTextHtml . $thumbnailHtml . '</div>',
				$record,
				$request
			)
		);
		$this->assignPreviewContentVariables($view, $item);
		return $view->render('Content');
	}

	/**
	 * Hook for subclasses to add variables to the "Content" preview template.
	 */
	protected function assignPreviewContentVariables(ViewInterface $view, GridColumnItem $item): void {}

	public function renderPageModulePreviewFooter(GridColumnItem $item): string
	{
		$record = $item->getRecord();
		$footerColumns = [
			'layout', 'frame_class', 'container_width', 'container_position', 'container_offset', 'media_layout',
		];
		$fieldsWithLabels = [];
		foreach ($footerColumns as $fieldName) {
			$default = $GLOBALS['TCA']['tt_content']['columns'][$fieldName]['config']['default'] ?? '';
			if (!$record->has($fieldName) || !$record->get($fieldName) || $record->get($fieldName) === $default) {
				continue;
			}
			$fieldsWithLabels[$fieldName] = $this->fieldProcessor->prepareFieldWithLabel($record, $fieldName);
		}
		$view = $this->createView($item->getContext()->getCurrentRequest());
		$view->assign('item', $item);
		$view->assign('record', $record);
		$view->assign('fieldsWithLabels', $fieldsWithLabels);
		return $view->render('Footer');
	}

	public function wrapPageModulePreview(string $header, string $content, GridColumnItem $item): string
	{
		return $header . $content;
	}
}

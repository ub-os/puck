<?php

namespace UBOS\Puck\Preview;


use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
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

/**
 * Default preview renderer for content elements.
 */
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
		$record = $this->recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $item->getRecord());
		$this->view->assign('item', $item);
		$this->view->assign('record', $record);
		$this->view->assign('editLink', $this->getEditLink($record));
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

		$record = $this->recordFactory->createResolvedRecordFromDatabaseRow('tt_content', $item->getRecord());
		$thumbnailHtml = '';
		foreach (['media', 'image', 'assets'] as $fieldName) {
			if ($record->has($fieldName) && $record->get($fieldName)) {
				$thumbnailHtml .= $this->getThumbCodeUnlinked($record->get($fieldName));
			}
		}

		$this->view->assign('item', $item);
		$this->view->assign('record', $record);
		$this->view->assign('editLink', $this->getEditLink($record));
		$this->view->assign('thumbnailHtml', $thumbnailHtml);
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
					$record->getUid() => 'edit'
				]
			],
			'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
		]);
	}

	protected function getThumbCodeUnlinked(iterable|FileReference $fileReferences, $size = 128): string
	{
		$thumbData = '';
		$fileReferences = $fileReferences instanceof FileReference ? [$fileReferences] : $fileReferences;
		foreach ($fileReferences as $fileReferenceObject) {
			// Do not show previews of hidden references
			if ($fileReferenceObject->getProperty('hidden')) {
				continue;
			}
			$fileObject = $fileReferenceObject->getOriginalFile();
			if ($fileObject->isMissing()) {
				$missingFileIcon = $this->getIconFactory()
					->getIcon('mimetypes-other-other', IconSize::MEDIUM, 'overlay-missing')
					->setTitle(static::getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:warning.file_missing') . ' ' . $fileObject->getName())
					->render();
				$thumbData .= '<div class="preview-thumbnails-element"><div class="preview-thumbnails-element-image">' . $missingFileIcon . '</div></div>';
				continue;
			}

			$imgTag = '';
			// Preview web image or media elements
			if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['thumbnails']
				&& ($fileReferenceObject->getOriginalFile()->isImage() || $fileReferenceObject->getOriginalFile()->isMediaFile())
			) {
				$cropVariantCollection = CropVariantCollection::create((string)$fileReferenceObject->getProperty('crop'));
				$cropArea = $cropVariantCollection->getCropArea();
				$taskType = ProcessedFile::CONTEXT_IMAGEPREVIEW;
				$processingConfiguration = [
					'width' => $size,
					'height' => $size,
				];
				if (!$cropArea->isEmpty()) {
					$taskType = ProcessedFile::CONTEXT_IMAGECROPSCALEMASK;
					$processingConfiguration = [
						'maxWidth' => $size,
						'maxHeight' => $size,
						'crop' => $cropArea->makeAbsoluteBasedOnFile($fileReferenceObject),
					];
				}
				$processedImage = $fileObject->process($taskType, $processingConfiguration);
				$attributes = [
					'src' => $processedImage->getPublicUrl() ?? '',
					'width' => $processedImage->getProperty('width'),
					'height' => $processedImage->getProperty('height'),
					'alt' => $fileReferenceObject->getAlternative() ?: $fileReferenceObject->getName(),
					'loading' => 'lazy',
				];
				$imgTag .= '<img ' . GeneralUtility::implodeAttributes($attributes, true) . '/>';
			} else {
			}
			$imgTag .= $this->getIconFactory()->getIconForResource($fileObject)->setTitle($fileObject->getName())->render();
			$thumbData .= '<div class="preview-thumbnails-element"><div class="preview-thumbnails-element-image">' . $imgTag . '</div></div>';
		}

		return $thumbData ? '<div class="preview-thumbnails" style="--preview-thumbnails-size: ' . $size . 'px">' . $thumbData . '</div>' : '';
	}

	protected function getIconFactory(): IconFactory
	{
		return GeneralUtility::makeInstance(IconFactory::class);
	}
}
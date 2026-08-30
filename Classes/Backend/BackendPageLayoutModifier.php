<?php

namespace UBOS\Puck\Backend;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use UBOS\Puck\Constants;

/**
 * Adds doktype specific header and footer content to the backend page layout.
 */
final class BackendPageLayoutModifier
{
	public function __construct(
		private readonly ViewFactoryInterface $viewFactory,
		private readonly RecordFactory $recordFactory,
	) {}

	#[AsEventListener]
	public function __invoke(ModifyPageLayoutContentEvent $event): void
	{
		$request = $event->getRequest();
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->loadJavaScriptModule(
			'@ubos/puck/Backend/web-layout-scroll.js'
		);
		$pageRenderer->loadJavaScriptModule(
			'@ubos/puck/Backend/web-layout-preview-container-toggle.js'
		);
		$pageRenderer->loadJavaScriptModule(
			'@ubos/puck/Backend/web-layout-content-minimize.js'
		);
		$row = BackendUtility::readPageAccess($request->getQueryParams()['id'], '1=1');
		$record = $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $row);
		$view = $this->viewFactory->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Templates/Backend/'],
				partialRootPaths: ['EXT:puck/Resources/Private/Templates/Backend/Partials'],
				request: $request,
			)
		);
		$view->assign('record', $record);
		$headerContent = '';
		if ((int)($row['doktype'] ?? 0) === Constants::DOKTYPES['news']) {
			$headerContent = $view->render('PageLayoutContent/Header/NewsPage');
		}
		$footerContent = $view->render('PageLayoutContent/Footer/Default');
		$event->addHeaderContent($headerContent);
		$event->addFooterContent($footerContent);
	}

}

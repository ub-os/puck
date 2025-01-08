<?php

namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use UBOS\Puck\Domain\Repository\PageRepository;

final class ModifyPageLayoutContent
{

	public function __construct(
		private readonly ViewFactoryInterface $viewFactory,
		protected RecordFactory               $recordFactory,
	)
	{
	}

	#[AsEventListener]
	public function __invoke(
		ModifyPageLayoutContentEvent $event
	): void
	{
		$request = $event->getRequest();
		$row = BackendUtility::readPageAccess($request->getQueryParams()['id'], true);
		$record = $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $row);
		$view = $this->viewFactory->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Fluid/Backend/Templates'],
				partialRootPaths: ['EXT:puck/Resources/Private/Fluid/Backend/Partials'],
				request: $request,
			)
		);
		$view->assign('record', $record);
		$headerContent = '';
		if ((int)($row['doktype'] ?? 0) === PageRepository::DOKTYPES['news']) {
			$headerContent = $view->render('PageLayoutContent/Header/NewsPage');
		}
		if ((int)($row['doktype'] ?? 0) === PageRepository::DOKTYPES['person']) {
			$headerContent = $view->render('PageLayoutContent/Header/PersonPage');
		}
		$footerContent = $view->render('PageLayoutContent/Footer/Default');
		$event->addHeaderContent($headerContent);
		$event->addFooterContent($footerContent);
	}

}
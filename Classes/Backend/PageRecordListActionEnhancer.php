<?php

namespace UBOS\Puck\Backend;

use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ActionGroup;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;

/**
 * Adds a button to the page record list to edit the content elements of the page.
 */
final class PageRecordListActionEnhancer
{
	public function __construct(
		private UriBuilder $uriBuilder,
		private IconFactory $iconFactory
	) {}

	#[AsEventListener]
	public function __invoke(ModifyRecordListRecordActionsEvent $event): void
	{
		if ($event->getRecord()->getMainType() !== 'pages') {
			return;
		}
		$record = $event->getRecord();
		$uid = $record['l10n_parent'] ?: $record['uid'];
		$uri = $this->uriBuilder->buildUriFromRoute(
			'web_layout',
			['id' => $uid, 'language' => $record['sys_language_uid']]
		);
		$button = (new LinkButton())
			->setHref($uri)
			->setTitle('Edit content')
			->setIcon(
				$this->iconFactory->getIcon('actions-document', IconSize::SMALL)
			);
		$event->setAction(
			$button,
			'showPageInLayoutModule',
			ActionGroup::primary,
			'',
			'edit'
		);

	}
}

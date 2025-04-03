<?php

namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Puck\Domain\ContentRecord;
use UBOS\Puck\Domain\PageRecord;

/**
 * Creates different record objects based on the main type of the record.
 */
final class RecordCreator
{

	#[AsEventListener]
	public function __invoke(RecordCreationEvent $event): void
	{
		if ($event->getRawRecord()->getMainType() === 'tt_content') {
			$event->setRecord(new ContentRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			));
		}
		if ($event->getRawRecord()->getMainType() === 'pages') {
			$event->setRecord(new PageRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			));
		}
	}
}

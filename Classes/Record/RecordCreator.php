<?php

namespace UBOS\Puck\Record;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;

/**
 * Creates different record objects based on the main type of the record.
 */
final class RecordCreator
{

	#[AsEventListener]
	public function __invoke(RecordCreationEvent $event): void
	{
		if ($event->getRawRecord()->getMainType() === 'tt_content') {
			$record = new ContentRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			);
			$record->setOverriddenProperties();
			$record->setComputedProperties();
			$event->setRecord($record);
		}
		if ($event->getRawRecord()->getMainType() === 'pages') {
			$record = new PageRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			);
			$record->setComputedProperties();
			$event->setRecord($record);
		}
	}
}

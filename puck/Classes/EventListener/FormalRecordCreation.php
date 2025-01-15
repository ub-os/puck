<?php

namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use UBOS\Puck\Domain\FieldRecord;

final class FormalRecordCreation
{

	#[AsEventListener]
	public function __invoke(RecordCreationEvent $event): void
	{
		if ($event->getRawRecord()->getMainType() === 'tx_formal_field') {
			$event->setRecord(new FieldRecord(
				$event->getRawRecord(),
				$event->getProperties(),
				$event->getSystemProperties()
			));
		}
	}
}

<?php

namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Domain\Event\RecordCreationEvent;
use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Puck\Domain\ContentRecord;

final class RecordCreation
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
    }
}

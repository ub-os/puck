<?php
namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

final class RestrictNewContentElementWizardItems {

    public function __construct(
    )
    {
    }

    #[AsEventListener]
    public function __invoke(
        ModifyNewContentElementWizardItemsEvent $event
    ): void
    {
//        foreach ($event->getWizardItems() as $key => $item) {
//            if (str_starts_with($key, 'default')
//                || str_starts_with($key, 'lists')
//                || str_starts_with($key, 'menu')
//                || str_starts_with($key, 'puckloader')
//                || $key == 'special_div'
//            ) {
//                $event->removeWizardItem($key);
//            }
//        }
    }
}
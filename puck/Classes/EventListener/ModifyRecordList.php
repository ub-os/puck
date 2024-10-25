<?php
namespace UBOS\Puck\EventListener;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListHeaderActionsEvent;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListTableActionsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

use TYPO3\CMS\Core\Utility\DebugUtility;

/**
 *
 */
final class ModifyRecordList {

    public function __construct(
        protected LoggerInterface $logger,
        protected UriBuilder $uriBuilder,
        protected IconFactory $iconFactory) {
    }

    #[AsEventListener]
    public function __invoke(ModifyRecordListRecordActionsEvent $event): void
    {
        $currentTable = $event->getTable();
        if ($currentTable === 'pages') {
            $currentRecord = $event->getRecord();
            $uri = $this->uriBuilder->buildUriFromRoute(
                'web_layout',
                ['id' => $currentRecord['uid']]
            );
            $icon = $this->iconFactory->getIcon('actions-document', Icon::SIZE_SMALL);
            $event->setAction(
                '<a aria-label="Edit content" title="Edit content" data-bs-toggle="tooltip" href="'.$uri.'" class="btn btn-default">'.$icon.'</a>',
                'showPageInLayoutModule',
                'primary',
                '',
                'edit'
            );
        }
    }
}
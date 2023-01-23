<?php
namespace UBOS\Puck\EventListener;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListHeaderActionsEvent;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\RecordList\Event\ModifyRecordListTableActionsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

use TYPO3\CMS\Core\Utility\DebugUtility;

/**
 *
 */
final class ModifyRecordList {

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    protected UriBuilder $uriBuilder;
    protected IconFactory $iconFactory;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, UriBuilder $uriBuilder, IconFactory $iconFactory) {
        $this->logger = $logger;
        $this->uriBuilder = $uriBuilder;
        $this->iconFactory = $iconFactory;
    }

    /**
     * @param ModifyRecordListRecordActionsEvent $event
     * @return void
     */
    public function modifyRecordActions(ModifyRecordListRecordActionsEvent $event): void
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
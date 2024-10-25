<?php
declare(strict_types=1);

namespace UBOS\Puck\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use Brotkrueml\Schema\Event\RegisterAdditionalTypePropertiesEvent;
use Brotkrueml\Schema\Model\Type\SearchAction;

final class AdditionalPropertiesForSearchAction
{
    #[AsEventListener]
    public function __invoke(RegisterAdditionalTypePropertiesEvent $event): void
    {
        if ($event->getType() === SearchAction::class) {
            $event->registerAdditionalProperty('query-input');
        }
    }
}
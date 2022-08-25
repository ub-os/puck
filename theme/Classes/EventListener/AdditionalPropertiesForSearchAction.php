<?php
declare(strict_types=1);

namespace UBOS\Theme\EventListener;

use Brotkrueml\Schema\Event\RegisterAdditionalTypePropertiesEvent;
use Brotkrueml\Schema\Model\Type\SearchAction;

final class AdditionalPropertiesForSearchAction
{
  public function __invoke(RegisterAdditionalTypePropertiesEvent $event): void
  {
    if ($event->getType() === SearchAction::class) {
      $event->registerAdditionalProperty('query-input');
    }
  }
}
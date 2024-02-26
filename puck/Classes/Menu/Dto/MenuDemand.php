<?php

namespace UBOS\Puck\Menu\Dto;

use TYPO3\CMS\Core\Utility\DebugUtility;
use UBOS\Puck\Utility\PuckUtility;

class MenuDemand
{
    public function __construct(
        public string $parents = '',
        public string $records = '',
        public ?int $limit = null,
        public int $offset = 0,
        /**
         * @var array<string, array{uids: string, conjunction: string}>
         */
        public array $categories = [],
        public string $categoriesConjunction = 'and',
        public string $orderField = 'sorting',
        public string $orderDirection = 'asc',
        public bool $orderByRecordsProperty = false,
        public array $additionalSettings = [],
    )
    {
    }
    
    public static function createFromSettingsArray(array $settings, array $additionalSettings = []): MenuDemand
    {
        $demand = new MenuDemand();
        $settings = PuckUtility::convertZeroStringsToInteger($settings, true);
        $demand->parents = $settings['demand']['parents'] ?? $demand->parents;
        $demand->records = $settings['demand']['records'] ?? $demand->records;
        $demand->limit = (int)$settings['demand']['limit'] ?? $demand->limit;
        $demand->offset = (int)$settings['demand']['offset'] ?? $demand->offset;
        $demand->categories = $settings['demand']['categories'] ?? $demand->categories;
        $demand->categoriesConjunction = $settings['demand']['categoriesConjunction'] ?? $demand->categoriesConjunction;
        $demand->orderField = $settings['order']['field'] ?? $demand->orderField;
        $demand->orderDirection = $settings['order']['direction'] ?? $demand->orderDirection;
        $demand->orderByRecordsProperty = $settings['order']['recordSelection'] ?? $demand->orderByRecordsProperty;
        $demand->additionalSettings = $additionalSettings;
        return $demand;
    }

}
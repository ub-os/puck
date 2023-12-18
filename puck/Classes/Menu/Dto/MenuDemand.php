<?php

namespace UBOS\Puck\Menu\Dto;

use UBOS\Puck\Utility\PuckUtility;

class MenuDemand
{
    public function __construct(
        public string $parents = '',
        public string $records = '',
        public ?int $limit = null,
        public int $offset = 0,
        public string $categories = '',
        public string $categoryConjunction = 'or',
        public string $categories2 = '',
        public string $categoryConjunction2 = 'or',
        public array $categoryBuckets = [
            'category' => [
                'uids' => '',
                'conjunction' => 'or'
            ],
            'category2' => [
                'uids' => '',
                'conjunction' => 'or'
            ]
        ],
        public string $bucketConjunction = 'and',
        public string $orderField = 'sorting',
        public string $orderDirection = 'asc',
        public array $additionalSettings = []
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
        $demand->categoryConjunction = $settings['demand']['categoryConjunction'] ?? $demand->categoryConjunction;
        $demand->categories2 = $settings['demand']['categories2'] ?? $demand->categories2;
        $demand->categoryConjunction2 = $settings['demand']['categoryConjunction2'] ?? $demand->categoryConjunction2;
        $demand->orderField = $settings['order']['field'] ?? $demand->orderField;
        $demand->orderDirection = $settings['order']['direction'] ?? $demand->orderDirection;
        $demand->additionalSettings = $additionalSettings;
        return $demand;
    }

}
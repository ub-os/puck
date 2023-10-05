<?php

namespace UBOS\Puck\Menu\Dto;

class CategoryFilterItem
{
    public function __construct(
        public string              $label,
        public string              $url = '',
        public ?FetchLinkOptions   $fetchLinkOptions = null,
        public bool                $active = false,
        public bool                $disabled = false,
        public bool                $hasNoPotential = false,
        public array               $activeItemsUids = [],
        /**
         * @var CategoryFilterItem[]
         */
        public array               $items = [],
        public ?CategoryFilterItem $closeItem = null
    )
    {
    }

}
<?php

namespace UBOS\Puck\Menu\Dto;

class CategoryFilter
{
    public function __construct(
        /**
         * @var CategoryFilterItem[]
         */
        public array $items = [],
        public int $buildTree = 1
    )
    {
    }

}
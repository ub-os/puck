<?php

namespace UBOS\Puck\Menu\Dto;

class Pagination
{
    public function __construct(
        public ?PaginationItem $prev = null,
        public ?PaginationItem $next = null,
        public ?PaginationItem $first = null,
        public ?PaginationItem $last = null,
        /**
         * @var PaginationItem[]
         */
        public array $items = [],
        public int $currentPage = 1,
        public bool $separatorLeft = false,
        public bool $separatorRight = false,
        public string $separatorString = '...',
        public ?PaginationItem $loadMore = null,
    ) {
    }
}
<?php

namespace UBOS\Puck\Menu\Dto;

class Pagination
{
	public function __construct(
		public int             $currentPage = 1,
		public ?PaginationItem $prev = null,
		public ?PaginationItem $next = null,
		/**
		 * @var PaginationItem[]
		 */
		public array           $items = [],
		public bool            $separatorLeft = false,
		public bool            $separatorRight = false,
		public string          $separatorString = '...',
		public ?PaginationItem $first = null,
		public ?PaginationItem $last = null,
		public ?PaginationItem $loadMore = null,
	)
	{
	}
}
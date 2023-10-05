<?php

namespace UBOS\Puck\Menu\Dto;

class PaginationItem
{
    public function __construct(
        public string            $label,
        public string            $url = '',
        public ?FetchLinkOptions $fetchLinkOptions = null,
        public bool              $active = false,
        public bool              $disabled = false,
    )
    {
    }

}
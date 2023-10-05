<?php

namespace UBOS\Puck\Menu\Dto;

class FetchLinkOptions
{
    public function __construct(
        public string $url,
        public string $contentId,
        public string $mode = 'replace',
        public bool $scrollToContent = true,
        public string $trigger = 'click'
    )
    {
    }
}
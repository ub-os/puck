<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

#[\Attribute]
class AsPlugin
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(
        public string $name,
		public int $fragmentTypeNum = 0,
		public bool $fragmentNoCache = false,
	)
    {
	}
}

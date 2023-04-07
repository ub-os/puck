<?php

declare(strict_types=1);

namespace UBOS\Puck\Attributes;

#[Attribute]
class ContainerElement
{
    public array $configuration;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }
}

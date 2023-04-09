<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

#[\Attribute]
class ContentElementWizard
{
    public string $tab;

    public int $order;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $tab, int $order = 10)
    {
        $this->tab = $tab;
        $this->order = $order;
    }
}

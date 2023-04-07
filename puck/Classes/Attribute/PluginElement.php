<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

#[\Attribute]
class PluginElement
{
    /**
     * @var array
     */
    public string $pluginName;

    public string $piFlexFormValue;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $pluginName, string $piFlexFormValue = '')
    {
        $this->pluginName = $pluginName;
        $this->piFlexFormValue = $piFlexFormValue;
    }
}

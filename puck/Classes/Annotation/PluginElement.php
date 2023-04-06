<?php

declare(strict_types=1);

namespace UBOS\Puck\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class PluginElement
{
    /**
     * @var array
     */
    public $pluginName;

    public $piFlexFormValue = '';

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $values)
    {
        if (isset($values['piFlexFormValue'])) {
            $this->piFlexFormValue = $values['piFlexFormValue'];
        }
        if (isset($values['pluginName'])) {
            $this->pluginName = $values['pluginName'];
        } elseif (isset($values['value'])) {
            $this->pluginName = $values['value'];
        }
    }
}

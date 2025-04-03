<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

/**
 * Add this attribute to controller action methods to mark them as the default action of the plugin.
 * @see AttributeReflection::configurePlugins() consumes this attribute for plugin configuration
 * @see AsPlugin necessary on controller class for this method attribute
 */
#[\Attribute]
class DefaultAction
{
    public function __construct()
    {
	}
}

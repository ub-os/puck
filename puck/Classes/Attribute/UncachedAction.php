<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

/**
 * Add this attribute to controller action methods to make them an uncached plugin action.
 * @see AttributeReflection::configurePlugins() consumes this attribute for plugin configuration
 * @see AsPlugin necessary on controller class for this method attribute
 */
#[\Attribute]
class UncachedAction
{
    public function __construct()
    {
	}
}

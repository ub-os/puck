<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

/**
 * Add this attribute to a controller class to automatically configure it as a plugin.
 * @see AttributeReflection::configurePlugins() consumes this attribute for plugin configuration
 * @see DefaultAction set on controller action method to be used as default action
 * @see UncachedAction set on controller actions that should not be cached
 */
#[\Attribute]
class AsPlugin
{
	/**
	 * @param string $name The plugin name
	 * @param int $fragmentTypeNum If not 0, pages of this type will render plugin as html fragment (default: 0)
	 * @param bool $fragmentNoCache Whether the plugin fragment should be uncached (default: false)
	 */
    public function __construct(
        public string $name,
		public int $fragmentTypeNum = 0,
		public bool $fragmentNoCache = false,
	)
    {
	}
}

<?php

declare(strict_types=1);

namespace UBOS\Puck\Attribute;

use Attribute;

/**
 * Add this attribute to controller action methods to automatically configure plugins
 * @see AttributeReflection::configurePlugins() consumes this attribute for plugin configuration
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsAction
{
	/**
	 * @param string $pluginName Name of the plugin to configure / add this action to
	 * @param bool $cacheable If false, this action will be added to nonCacheableActions (default: false)
	 * @param bool $defaultAction If true, this action will be the default action (default: false)
	 */
	public function __construct(
		public string $pluginName,
		public bool $cacheable = true,
		public bool $defaultAction = false,
	) {}
}

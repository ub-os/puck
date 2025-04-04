<?php
declare(strict_types=1);

namespace UBOS\Puck\Attribute;
use Attribute;

/**
 * Add this attribute to controller action methods to automatically configure plugins
 * @see AttributeReflection::configurePlugins() consumes this attribute for plugin configuration
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsAction
{

	/**
	 * @param string $pluginName Name of the plugin to configure / add this action to
	 * @param bool $uncached If true, this action will be uncached (default: false)
	 * @param bool $default If true, this action will be the default action (default: false)
	 * @param int $pluginFragmentPageType If not 0, pages of this type will render the configured plugin as html fragment (default: 0)
	 */
    public function __construct(
        public string $pluginName,
		public bool $uncached = false,
		public bool $default = false,
		public int $pluginFragmentPageType = 0,
	)
    {
	}
}

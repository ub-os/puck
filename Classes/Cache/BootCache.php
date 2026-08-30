<?php

namespace UBOS\Puck\Cache;

use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Core\Bootstrap;

/**
 * Minimal get-or-build cache usable during ext_localconf.php / TCA loading,
 * where the CacheManager is not yet available.
 *
 * Isolates the single @internal core call (Bootstrap::createCache) so puck's
 * declarative configuration classes can cache their reflection / file-scan
 * results without spreading bootstrap plumbing across ext_localconf.php.
 */
final class BootCache
{
	private const IDENTIFIER = 'puck';

	/**
	 * Returns the cached value for $key, building and storing it via $builder on a miss.
	 * $builder must return var_export()-able data (arrays / scalars).
	 *
	 * @template T
	 * @param callable(): T $builder
	 * @return T
	 */
	public static function get(string $key, callable $builder): mixed
	{
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][self::IDENTIFIER] ??= [
			'frontend' => PhpFrontend::class,
			'backend' => SimpleFileBackend::class,
			'groups' => ['system'],
		];

		$cache = Bootstrap::createCache(self::IDENTIFIER);
		if (!$cache instanceof PhpFrontend) {
			// Integrator reconfigured the cache with a non-PHP backend - build live.
			return $builder();
		}

		$entry = $cache->require($key);
		if (is_array($entry) && array_key_exists('value', $entry)) {
			return $entry['value'];
		}

		$value = $builder();
		$cache->set($key, 'return ' . var_export(['value' => $value], true) . ';');
		return $value;
	}
}

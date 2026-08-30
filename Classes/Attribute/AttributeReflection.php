<?php

namespace UBOS\Puck\Attribute;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use UBOS\Puck\Cache\BootCache;

/**
 * Utility class that scans directories for PHP classes with specific attributes
 * and performs configuration tasks based on those attributes.
 * @see AsAction Attribute on controller methods to configure plugins
 * @see Persistence Attribute on model classes and properties to configure Extbase persistence mapping
 */
class AttributeReflection
{
	/**
	 * Configures Plugins by scanning controller class methods for the AsAction attribute
	 * Registers plugins with ExtensionUtility::configurePlugin()
	 *
	 * @param string $extensionKey The extension key
	 * @param string $controllerDirectory Relative path to the controllers directory
	 * @param string $controllerNamespace Namespace prefix for the controllers
	 */
	public static function configurePlugins(
		string $extensionKey,
		string $controllerDirectory,
		string $controllerNamespace
	): void {
		$pluginConfigs = BootCache::get(
			'plugin_configs_' . $extensionKey,
			static fn(): array => self::buildPluginConfigs($extensionKey, $controllerDirectory, $controllerNamespace)
		);
		foreach ($pluginConfigs as $name => $plugin) {
			ExtensionUtility::configurePlugin(
				$extensionKey,
				$name,
				$plugin['actions'],
				$plugin['nonCacheableActions'],
				ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
			);
		}
	}

	/**
	 * Scans controller classes for the AsAction attribute and returns the plugin
	 * configuration as a plain, serialisable array (pluginName => ['actions' => ...,
	 * 'nonCacheableActions' => ...]).
	 *
	 * Split from configurePlugins() so the reflection-heavy result can be cached
	 * (see BootCache) and only the cheap configurePlugin() replay runs per request.
	 *
	 * @return array<string, array{actions: array<string, string>, nonCacheableActions: array<string, string>}>
	 */
	public static function buildPluginConfigs(
		string $extensionKey,
		string $controllerDirectory,
		string $controllerNamespace
	): array {
		$plugins = [];
		self::reflectDirectory(
			$extensionKey,
			$controllerDirectory,
			$controllerNamespace,
			function (\ReflectionClass $reflection, string $className) use (&$plugins) {
				foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
					if (!str_contains($method->getName(), 'Action')) {
						continue;
					}
					$actionAttributes = $method->getAttributes(AsAction::class);
					foreach ($actionAttributes as $actionAttribute) {
						$action = $actionAttribute->newInstance();
						$actionName = str_replace('Action', '', $method->getName());
						$pluginName = $action->pluginName;

						// create new configuration array for plugin if it does not exist
						if (!($plugins[$pluginName] ?? false)) {
							$plugins[$pluginName] = [
								'actions' => [],
								'nonCacheableActions' => [],
							];
						}

						// add action to plugin configuration
						if (!($plugins[$pluginName]['actions'][$className] ?? false)) {
							if ($action->defaultAction) {
								$plugins[$pluginName]['actions'] = [$className => $actionName] + $plugins[$pluginName]['actions'];
							} else {
								$plugins[$pluginName]['actions'][$className] = $actionName;
							}
						} elseif ($action->defaultAction) {
							$plugins[$pluginName]['actions'] = [$className => $actionName . ',' . $plugins[$pluginName]['actions'][$className]] + $plugins[$pluginName]['actions'];
						} else {
							$plugins[$pluginName]['actions'][$className] .= ',' . $actionName;
						}
						if (!$action->cacheable && !($plugins[$pluginName]['nonCacheableActions'][$className] ?? false)) {
							$plugins[$pluginName]['nonCacheableActions'][$className] = $actionName;
						} elseif (!$action->cacheable) {
							$plugins[$pluginName]['nonCacheableActions'][$className] .= ',' . $actionName;
						}
					}
				}
			}
		);
		return $plugins;
	}

	/**
	 * Creates Extbase persistence mapping configuration by scanning model classes
	 *
	 * @param string $extensionKey The extension key
	 * @param string $modelDirectory Relative path to the models directory
	 * @param string $modelNamespace Namespace prefix for the models
	 * @return array The generated persistence mapping configuration
	 */
	public static function createExtbasePersistenceMapping(
		string $extensionKey,
		string $modelDirectory,
		string $modelNamespace
	): array {
		$mapping = [];
		self::reflectDirectory(
			$extensionKey,
			$modelDirectory,
			$modelNamespace,
			function (\ReflectionClass $reflection, string $className) use (&$mapping) {
				$classAttribute = $reflection->getAttributes(Persistence::class)[0] ?? null;
				if (!$classAttribute) {
					return;
				}
				$mapping[$className] = [
					'tableName' => $classAttribute->newInstance()->name,
					'properties' => [],
				];
				foreach ($reflection->getProperties() as $property) {
					$propertyAttribute = $property->getAttributes(Persistence::class)[0] ?? null;
					if (!$propertyAttribute) {
						continue;
					}
					$mapping[$className]['properties'][$property->getName()] = [
						'fieldName' => $propertyAttribute->newInstance()->name,
					];
				}
			},
		);
		return $mapping;
	}

	/**
	 * Helper method that performs reflection on PHP classes in a directory
	 */
	protected static function reflectDirectory(
		string $extensionKey,
		string $relativeDirectory,
		string $namespace,
		callable $callback,
		string $excludePattern = ''
	): void {
		$directory = ExtensionManagementUtility::extPath($extensionKey, $relativeDirectory);
		if (!str_ends_with($directory, '/')) {
			$directory .= '/';
		}
		if (!str_ends_with($namespace, '\\')) {
			$namespace .= '\\';
		}
		$files = GeneralUtility::getAllFilesAndFoldersInPath(
			[],
			path: $directory,
			extList: 'php',
			excludePattern: $excludePattern
		);
		foreach ($files as $file) {
			$path = str_replace('.php', '', $file);
			$className = $namespace . str_replace('/', '\\', explode($directory, $path)[1]);
			$reflection = new \ReflectionClass($className);
			if ($reflection->isTrait() || $reflection->isInterface() || $reflection->isAbstract()) {
				continue;
			}
			$callback($reflection, $className);
		}
	}
}

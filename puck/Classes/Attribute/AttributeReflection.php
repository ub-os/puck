<?php

namespace UBOS\Puck\Attribute;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Utility class that scans directories for PHP classes with specific attributes
 * and performs configuration tasks based on those attributes.
 * @see AsPlugin Attribute on controller classes that should be configured as plugins
 * @see DefaultAction Attribute on controller action method to be used as default action
 * @see UncachedAction Attribute on controller actions that should not be cached
 * @see Persistence Attribute on model classes and properties to configure Extbase persistence mapping
 */
class AttributeReflection
{
	/**
	 * Configures Plugins by scanning controller classes for the AsPlugin attribute
	 *
	 * @param string $extensionKey The extension key
	 * @param string $controllerDirectory Relative path to the controllers directory
	 * @param string $controllerNamespace Namespace prefix for the controllers
	 */
	public static function configurePlugins(
		string $extensionKey,
		string $controllerDirectory,
		string $controllerNamespace
	): void
	{
		self::reflectDirectory(
			$extensionKey,
			$controllerDirectory,
			$controllerNamespace,
			function(\ReflectionClass $reflection, string $className) use ($extensionKey) {
				$pluginAttribute = $reflection->getAttributes(AsPlugin::class)[0] ?? null;
				if (!$pluginAttribute) {
					return;
				}

				$pluginConfig = $pluginAttribute->newInstance();
				$actions = [$className => ''];
				$noCacheActions = [$className => ''];

				foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
					if (!str_contains($method->getName(), 'Action')) {
						continue;
					}
					$actionName = str_replace('Action', '', $method->getName());
					if ($method->getAttributes(DefaultAction::class)[0] ?? null) {
						$actions[$className] = $actionName . ',' . $actions[$className];
					} else {
						$actions[$className] .= $actionName . ',';
					}
					if ($method->getAttributes(UncachedAction::class)[0] ?? null) {
						$noCacheActions[$className] .= $actionName . ',';
					}
				}

				ExtensionUtility::configurePlugin(
					$extensionKey,
					$pluginConfig->name,
					$actions,
					$noCacheActions,
					'CType'
				);

				if ($pluginConfig->fragmentTypeNum) {
					ExtensionManagementUtility::addTypoScript(
						$extensionKey,
						'setup',
						'
					' . $pluginConfig->name . 'PluginFragmentPage = PAGE
					' . $pluginConfig->name . 'PluginFragmentPage {
						typeNum = ' . $pluginConfig->fragmentTypeNum . '
						20 = USER
						20 {
							userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
							extensionName = ' . ucFirst($extensionKey) . '
							pluginName = ' . $pluginConfig->name . '
						}
						meta {
							robots = noindex, nofollow
							robots.replace = 1
						}
						config {
							disableAllHeaderCode = 1
							debug = 0
							admPanel = 0
							index_enable = 0
							no_cache = ' . ($pluginConfig->fragmentNoCache ? '1' : '0') . '
						}
					}
				',
						'defaultContentRendering'
					);
				}
			},
		);
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
	): array
	{
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
	): void
	{
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
			$callback($reflection, $className);
		}
	}
}
<?php

namespace UBOS\Puck\Attribute;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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
	 * Sets up TypoScript for plugin html fragment rendering if AsAction::pluginFragmentPageType is set.
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
		$plugins = [];
		self::reflectDirectory(
			$extensionKey,
			$controllerDirectory,
			$controllerNamespace,
			function(\ReflectionClass $reflection, string $className) use ($extensionKey, &$plugins) {
				foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
					if (!str_contains($method->getName(), 'Action')) {
						continue;
					}
					$actionAttributes = $method->getAttributes(AsAction::class);
					foreach ($actionAttributes as $actionAttribute) {
						$action = $actionAttribute->newInstance();
						$actionName = str_replace('Action', '', $method->getName());
						$pluginName = $action->pluginName;

						if (!($plugins[$pluginName] ?? false)) {
							$plugins[$pluginName] = [
								'actions' => [$className => ''],
								'noCacheActions' => [$className => ''],
								'pluginFragmentPageType' => 0
							];
						}
						$plugins[$pluginName]['pluginFragmentPageType'] = $action->pluginFragmentPageType ?: $plugins[$pluginName]['pluginFragmentPageType'];

						if ($action->default) {
							$plugins[$pluginName]['actions'][$className] = $actionName . ',' . $plugins[$pluginName]['actions'][$className];
						} else {
							$plugins[$pluginName]['actions'][$className] .= $actionName . ',';
						}
						if ($action->uncached) {
							$plugins[$pluginName]['noCacheActions'][$className] .= $actionName . ',';
						}
					}
				}
			}
		);
		foreach ($plugins as $name => $plugin) {
			ExtensionUtility::configurePlugin(
				$extensionKey,
				$name,
				$plugin['actions'],
				$plugin['noCacheActions'],
				'CType'
			);

			if ($plugin['pluginFragmentPageType']) {
				ExtensionManagementUtility::addTypoScript(
					$extensionKey,
					'setup',
					'
					' . $name . 'PluginFragmentPage = PAGE
					' . $name . 'PluginFragmentPage {
						typeNum = ' . $plugin['pluginFragmentPageType'] . '
						20 = EXTBASEPLUGIN
						20 {
							extensionName = ' . ucFirst($extensionKey) . '
							pluginName = ' . $name . '
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
						}
					}
				',
					'defaultContentRendering'
				);
			}
		}
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
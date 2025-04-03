<?php

namespace UBOS\Puck\Attribute;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class AttributeReflection
{
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
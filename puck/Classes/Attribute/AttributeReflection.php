<?php

namespace UBOS\Puck\Attribute;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class AttributeReflection
{
	public static function configurePlugins($extensionKey, $directory, $namespace): void
	{
		$controllerPaths = GeneralUtility::getAllFilesAndFoldersInPath(
			[],
			path: $directory,
			extList: 'php',
			excludePattern: '^(?!.*Controller\.php$).*$'
		);
		foreach ($controllerPaths as $path) {
			$path = str_replace('.php', '', $path);
			$className = $namespace . str_replace('/', '\\', explode($directory, $path)[1]);
			$reflection = new \ReflectionClass($className);
			$pluginAttribute = $reflection->getAttributes(AsPlugin::class)[0] ?? null;
			if (!$pluginAttribute) {
				continue;
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
				$ts = '
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
				';
				DebugUtility::debug($ts);
				ExtensionManagementUtility::addTypoScript(
					$extensionKey,
					'setup',
					$ts,
					'defaultContentRendering'
				);
			}
		}
	}

	public static function createExtbasePersistenceMapping($extensionKey, $directory, $namespace): array
	{
		$modelPaths = GeneralUtility::getAllFilesAndFoldersInPath(
			[],
			path: $directory,
			extList: 'php',
//			excludePattern: '^(?!.*Model\.php$).*$'
		);
		$mapping = [];
		foreach ($modelPaths as $path) {
			$path = str_replace('.php', '', $path);
			$className = $namespace . str_replace('/', '\\', explode($directory, $path)[1]);
			$reflection = new \ReflectionClass($className);
			$classAttribute = $reflection->getAttributes(Persistence::class)[0] ?? null;
			if (!$classAttribute) {
				continue;
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
		}
		return $mapping;
	}
}
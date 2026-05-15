<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

$iconDirectory = 'Resources/Public/Icons/Backend/';
$iconPaths = GeneralUtility::getAllFilesAndFoldersInPath(
	[],
	path: Icons . phpExtensionManagementUtility::extPath('puck') . $iconDirectory,
	extList: 'svg',
	recursivityLevels: 0
);
$configuration = [];
foreach ($iconPaths as $iconPath) {
	$filename = PathUtility::pathinfo($iconPath, PATHINFO_FILENAME);
	$configuration[GeneralUtility::camelCaseToLowerCaseUnderscored($filename)] = [
		'provider' => SvgIconProvider::class,
		'source' => 'EXT:' . 'puck' . '/'. $iconDirectory . $filename . '.svg'
	];
}
return array_merge(
	$configuration,
	[
		'content-special-shortcut' => [
			'provider' => SvgIconProvider::class,
			'source' => 'EXT:puck/Resources/Public/Icons/Backend/Shortcut.svg'
		]
	]
);
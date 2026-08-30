<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['sys_category']['columns']['slug'] = [
	'label' => 'URL segment',
	'config' => [
		'type' => 'slug',
		'generatorOptions' => [
			'fields' => ['title'],
			'fieldSeparator' => '/',
			'replacements' => [
				'/' => '-',
			],
		],
		'fallbackCharacter' => '-',
		'eval' => 'uniqueInSite',
		'default' => '',
	],
];

ExtensionManagementUtility::addToAllTCAtypes(
	'sys_category',
	'slug',
	'',
	'after:title'
);

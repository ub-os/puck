<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

try {
	$iconJson = file_get_contents(ExtensionManagementUtility::extPath('puck') . 'Resources/Public/Fonts/Icons/icons.json');
	$iconArray = json_decode($iconJson, true);
} catch (Exception $e) {
	$iconArray = [];
}

$iconJsonIterator = new RecursiveIteratorIterator(
	new RecursiveArrayIterator($iconArray),
	RecursiveIteratorIterator::SELF_FIRST
);
$iconSelectItems = [['label' => 'none', 'value' => '']];
foreach ($iconJsonIterator as $key => $val) {
	$iconSelectItems[] = [
		'label' => $key,
		'value' => $key,
		'icon' => 'EXT:puck/Resources/Public/Icons/Frontend/' . $key . '.svg',
	];
}
return [
	'label' => 'Icon',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'items' => $iconSelectItems,
		'default' => '',
		'behaviour' => [
			'allowLanguageSynchronization' => true,
		],
	],
];

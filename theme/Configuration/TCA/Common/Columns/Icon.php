<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$iconJson = file_get_contents(ExtensionManagementUtility::extPath('theme') . "Resources/Public/Fonts/Icons/icons.json");

$iconJsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($iconJson, TRUE)),
    RecursiveIteratorIterator::SELF_FIRST);
$iconSelectItems = array(['none', '']);
foreach ($iconJsonIterator as $key => $val) {
    $iconSelectItems[] = [
        $key, $key, 'EXT:theme/Resources/Public/Icons/Frontend/' . $key . '.svg'
    ];
}
return [
    'label' => 'Icon',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $iconSelectItems,
        'default' => '',
    ]
];
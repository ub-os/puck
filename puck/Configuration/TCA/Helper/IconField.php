<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$iconJson = file_get_contents(ExtensionManagementUtility::extPath('puck') . "Resources/Public/Fonts/Icons/icons.json");

$iconJsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($iconJson, TRUE)),
    RecursiveIteratorIterator::SELF_FIRST);
$iconSelectItems = array(['label' => 'none', 'value' => '']);
foreach ($iconJsonIterator as $key => $val) {
    $iconSelectItems[] = [
        'label' => $key,
        'value' => $key,
        'icon' => 'EXT:puck/Resources/Public/Icons/Frontend/' . $key . '.svg'
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
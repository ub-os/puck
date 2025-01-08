<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility as ExtUtil;

foreach (glob(ExtUtil::extPath('puck', 'Configuration/TCA/Overrides/pages/*.php')) as $file) {
	include $file;
}
foreach (glob(ExtUtil::extPath('puck', 'Configuration/PageTypes/*.php')) as $file) {
	(include $file)?->addTCA();
}

$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-news'] = 'news_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-categories'] = 'category_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-persons'] = 'person_folder';

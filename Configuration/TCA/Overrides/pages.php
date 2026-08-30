<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility as ExtUtil;
use UBOS\Puck\Configuration\PageTypeConfiguration;

foreach (glob(ExtUtil::extPath('puck', 'Configuration/TCA/Overrides/pages/*.php')) ?: [] as $file) {
	include $file;
}
foreach (glob(ExtUtil::extPath('puck', 'Configuration/PageTypes/*.php')) ?: [] as $file) {
	$conf = (include $file);
	if ($conf instanceof PageTypeConfiguration) {
		$conf->addTCA();
	}
}

$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-news'] = 'news_folder';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-content'] = 'apps-pagetree-folder-contains-news';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-categories'] = 'mimetypes-x-sys_category';
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-shape'] = 'mimetypes-x-content-form';

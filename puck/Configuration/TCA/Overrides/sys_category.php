<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['sys_category']['columns']['slug'] = $GLOBALS['TCA']['pages']['columns']['slug'];
ExtensionManagementUtility::addToAllTCAtypes(
	'sys_category',
	'slug',
	'',
	'after:title'
);
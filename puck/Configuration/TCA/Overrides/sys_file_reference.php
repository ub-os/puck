<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puckloader\Utility\TcaUtility;

$GLOBALS['TCA']['sys_file_reference']['columns']['crop']['config']['cropVariants'] = [];
$GLOBALS['TCA']['sys_file_reference']['palettes']['videoOverlayPalette']['showitem'] = 'title,description';

$GLOBALS['TCA']['sys_file_reference']['columns']['breakpoints'] = [
	'label' => 'Crop breakpoint variants',
	'description' => 'Select which breakpoint crop variants should be used. "Default" is always active and the fallback when no breakpoint applies.',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectCheckBox',
		'items' => TcaUtility::selectItemsHelper([
			['Phone (<540px)', 'xs'],
			['Tablet (<900px)', 'm'],
			['Laptop (<1340px)', 'xl'],
		]),
		'dbFieldLength' => 10,
		'default' => 'default',
	],
];

$GLOBALS['TCA']['sys_file_reference']['palettes']['imageoverlayPaletteWithBreakpoints'] = [
	'showitem' => $GLOBALS['TCA']['sys_file_reference']['palettes']['imageoverlayPalette']['showitem'] . ',breakpoints',
];

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
            ['Phone (<450px)', 'xs'],
            ['Tablet (<975px)', 'm'],
            ['Laptop (<1325px)', 'l'],
        ]),
        'default' => 'default',
    ],
];

$GLOBALS['TCA']['sys_file_reference']['palettes']['imageoverlayPaletteWithBreakpoints'] = [
    'showitem' => $GLOBALS['TCA']['sys_file_reference']['palettes']['imageoverlayPalette']['showitem'].',breakpoints',
];

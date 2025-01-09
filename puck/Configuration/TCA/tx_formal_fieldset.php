<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puckloader\Utility\TcaUtility;

$ctrl = [
	'label' => 'title',
	'label_alt' => 'type',
	'label_alt_force' => true,
	'title' => 'Fieldset',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/Default.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'title',
	'type' => 'type'
];
$interface = [];
$columns = [
	'title' => [
		'label' => 'Title',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'form' => [
		'label' => 'Form',
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_formal_form',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'fields' => [
		'label' => 'Fields',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_formal_field',
			'foreign_field' => 'fieldset',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
		],
	],
	'type' => [
		'label' => 'Type',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => TcaUtility::selectItemsHelper([
				['Fieldset', 'fieldset'],
				['Pagination step', 'step'],
			]),
			'default' => 'fieldset',
		]
	],
	'prev_label' => [
		'label' => 'Previous button label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'next_label' => [
		'label' => 'Next button label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
];
$palettes = [
	'title' => [
		'showitem' => 'title, type',
	],
	'step-labels' => [
		'showitem' => 'prev_label, next_label, type',
	]
];
$baseShowItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
        sys_language_uid, 
        l10n_parent, 
        l10n_diffsource, 
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
        hidden';

return [
	'ctrl' => $ctrl,
	'interface' => $interface,
	'columns' => $columns,
	'palettes' => $palettes,
	'types' => [
		'0' => [
			'showitem' => 'type,'.$baseShowItem
		],
		'fieldset' => [
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;title,
					fields,'
				.$baseShowItem,
		],
		'step' => [
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;step-labels,'
				.$baseShowItem,
		],
	],
];

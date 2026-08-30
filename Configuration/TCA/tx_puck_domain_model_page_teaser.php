<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ctrl = [
	'label' => 'title',
	'title' => 'Page teaser',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'sortby' => 'sorting',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'delete' => 'deleted',
	'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/PageTeaser.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'title, text',
	'security' => [
		'ignorePageTypeRestriction' => true,
	],
	'hideTable' => false
];
$interface = [
];

$columns = [
	'title' => [
		'label' => 'Title',
		'config' => [
			'type' => 'input',
			'size' => 255,
			'eval' => 'trim,required',
		]
	],
	'text' => [
		'label' => 'Text',
		'config' => [
			'type' => 'text',
			'cols' => 40,
			'rows' => 15,
			'eval' => 'trim',
		]
	],
	'media' => [
		'label' => 'Media',
		'config' => [
			'type' => 'file',
			'allowed' => 'common-image-types',
		],
	],
	'icon' => [
		'label' => 'Icon',
		'config' => [
			'type' => 'text',
		],
	],
	'page' => [
		'label' => 'Page',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
			],
			'foreign_table' => 'pages',
			'disableNoMatchingValueElement' => true,
			'foreign_table_where' => 'AND (pages.uid = ###REC_FIELD_pid### OR pages.l10n_parent = ###REC_FIELD_pid###) AND pages.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)',
		],
	],
];

$palettes = [
	'teaser' => [
		'showitem' => '',
	],
];

$types = [
	'0' => [
		'showitem' => '
                --div--;General, 
                page, title, text, media,
             	',
	],
];

return [
	'ctrl' => $ctrl,
	'interface' => $interface,
	'columns' => $columns,
	'palettes' => $palettes,
	'types' => $types,
];

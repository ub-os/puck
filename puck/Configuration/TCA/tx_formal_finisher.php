<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\TcaUtility;

$ctrl = [
	'label' => 'type',
	'title' => 'Form finisher',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/Default.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'title',
	'security' => [
		'ignorePageTypeRestriction' => true,
	],
	'type' => 'type'
];
$interface = [];
$columns = [
	'plugin_uid' => [
		'label' => 'Plugin',
		'config' => [
			'type' => 'group',
			'allowed' => 'tt_content',
			'size' => 1,
			'maxitems' => 1
		],
	],
	'type' => [
		'label' => 'Type',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['', ''],
				['Save submission to database', 'UBOS\Puck\Domain\Finisher\SaveSubmissionFinisher'],
				['Mail consent process', 'UBOS\Puck\Domain\Finisher\ConsentFinisher'],
				['Save to any table', 'UBOS\Puck\Domain\Finisher\SaveToAnyTableFinisher'],
				['Send email', 'UBOS\Puck\Domain\Finisher\SendEmailFinisher'],
				['Redirect', 'UBOS\Puck\Domain\Finisher\RedirectFinisher'],
			]),
		],
	],
	'condition' => [
		'label' => 'Condition',
		'config' => [
			'type' => 'input',
			//'renderType' => 'codeEditor',
			//'format' => 'javascript',
			//'rows' => 1,
			'size' => 100,
			'valuePicker' => [
				'items' => [
					['Field value is true / not empty', 'formValue("field-identifier")'],
					['Field value is equal to', 'formValue("field-identifier") == "some-value"'],
					['Consent was approved', 'isConsentApproved()'],
					['Consent was dismissed', 'isConsentDismissed()'],
				],
			],
		],
	],
	'settings' => [
		'label' => 'Settings',
		'displayCond' => 'FIELD:type:REQ:true',
		'config' => [
			'type' => 'flex',
			'ds' => [
				'default' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/Default.xml',
				'UBOS\Puck\Domain\Finisher\ConsentFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/ConsentFinisher.xml',
				'UBOS\Puck\Domain\Finisher\SaveSubmissionFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/SaveSubmissionFinisher.xml',
				'UBOS\Puck\Domain\Finisher\SaveToAnyTableFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/SaveToAnyTableFinisher.xml',
				'UBOS\Puck\Domain\Finisher\SendEmailFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/SendEmailFinisher.xml',
			],
			'ds_pointerField' => 'type',
		],
	],
];
$palettes = [
	'base' => [
		'showitem' => 'type, --linebreak--, condition',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
		--palette--;;base,
        settings,';

return [
	'ctrl' => $ctrl,
	'interface' => $interface,
	'columns' => $columns,
	'palettes' => $palettes,
	'types' => [
		'0' => [
			'showitem' => $showItem,
		],
	],
];

<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\TcaUtility;

$ctrl = [
	'label' => 'label',
	'title' => 'Field',
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
	'searchFields' => 'label',
	'type' => 'type'

];
$interface = [];
$columns = [
	'label' => [
		'label' => 'Label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
			'required' => true,
		],
	],
	'description' => [
		'label' => 'Description',
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'placeholder' => [
		'label' => 'Placeholder',
		'displayCond' => 'FIELD:type:IN:text,textarea,email,number,tel,password,url',
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'type' => [
		'label' => 'Type',
		'onChange' => 'reload',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Text field', 'text', '', 'basic'],
				['Textarea', 'textarea', '', 'basic'],
				['Select', 'select', '', 'basic'],
				['Single checkbox', 'single-checkbox', '', 'basic'],
				['Checkboxes', 'checkbox', '', 'basic'],
				['Radio buttons', 'radio', '', 'basic'],
				['Email', 'email', '', 'typed-text-inputs'],
				['Number', 'number', '', 'typed-text-inputs'],
				['Phone number', 'tel', '', 'typed-text-inputs'],
				['Password', 'password', '', 'typed-text-inputs'],
				['URL', 'url', '', 'typed-text-inputs'],
				['Color', 'color', '', 'special'],
				['Range', 'range', '', 'special'],
				['Date', 'date', '', 'special'],
				['File', 'file', '', 'special'],
				['Reset', 'reset', '', 'special'],
				['Captcha', 'captcha', '', 'special'],
				['Country select', 'country', '', 'special'],
				['Hidden input', 'hidden', '', 'special'],
				['Rich text content', 'rte', '', 'no-input'],
				['Content element', 'content', '', 'no-input'],
			]),
			'itemGroups' => [
				'basic' => 'Basic',
				'typed-text-inputs' => 'Typed text fields',
				'special' => 'Special',
				'no-input' => 'Content only / No input',
			]
		],
	],
	'default_value' => [
		'label' => 'Default value',
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'fieldset' => [
		'label' => 'Fieldset',
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_formal_fieldset',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'identifier' => [
		'label' => 'Identifier',
		'config' => [
			'type' => 'slug',
			'generatorOptions' => [
				'fields' => ['label'],
				'fieldSeparator' => '-',
				'replacements' => [
					'/' => '',
				],
			],
			'appearance' => [
				'prefix' => \UBOS\Puck\UserFunctions\FormEngine\SlugPrefix::class . '->getHash',
			],
			'fallbackCharacter' => '-',
			'eval' => 'uniqueInPid',
			'default' => '',
		],
	],
	'required' => [
		'label' => 'Required',
		'config' => [
			'type' => 'check',
		],
	],
	'field_options' => [
		'label' => 'Options',
		'displayCond' => 'FIELD:type:IN:select,checkbox,radio',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_formal_field_option',
			'foreign_field' => 'field',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
		],
	],
	'validation' => [
		'label' => 'Validation',
		'onChange' => 'reload',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Auto', ''],
				['Length', 'length'],
				['RegEx', 'regex'],
			]),
		],
	],
	'validation_config' => [
		'label' => 'Validation Configuration',
		'displayCond' => 'FIELD:validation:IN:length,regex',
		'config' => [
			'type' => 'input',
		],
	],
	'layout' => [
		'label' => 'Layout',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Default', 'default'],
			]),
		],
	],
	'width' => [
		'label' => 'Width (%)',
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'default' => 100,
			'size' => 30,
			'range' => [
				'lower' => 20,
				'upper' => 100
			],
			'valuePicker' => [
				'items' => [
					['20', 20],
					['25', 25],
					['33', 33],
					['50', 50],
					['66', 66],
					['75', 75],
					['100', 100],
				],
			],
		],
	],
	'css_class' => [
		'label' => 'CSS Class',
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'disabled' => [
		'label' => 'Disabled',
		'config' => [
			'type' => 'check',
		],
	],
	'readonly' => [
		'label' => 'Readonly',
		'config' => [
			'type' => 'check',
		],
	],
	'pattern' => [
		'label' => 'RegEx pattern',
		'displayCond' => 'FIELD:type:IN:text,textarea,email,tel,password,url',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'maxlength' => [
		'label' => 'Maxlength',
		'displayCond' => 'FIELD:type:IN:text,textarea,email,tel,password,url',
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'min' => [
		'label' => 'Min',
		'displayCond' => 'FIELD:type:IN:number,range,date',
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
		],
	],
	'max' => [
		'label' => 'Max',
		'displayCond' => 'FIELD:type:IN:number,range,date',
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
		],
	],
	'step' => [
		'label' => 'Step',
		'displayCond' => 'FIELD:type:IN:number,range',
		'config' => [
			'type' => 'number',
			'format' => 'decimal',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'display_condition' => [
		'label' => 'Display condition',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],

];
$palettes = [
	'base' => [
		'showitem' => '
		label, identifier, 
		--linebreak--, 
		type, default_value, required, 
		--linebreak--,
		field_options,
		--linebreak--, 
		description, placeholder',
	],
	'config' => [
		'showitem' => '',
	],
	'detail' => [
		'showitem' => '',
	],
	'layout' => [
		'label' => 'Layout',
		'showitem' => 'layout, css_class, --linebreak--, width',
	],
	'attributes' => [
		'label' => 'Attributes',
		'showitem' => 'disabled, readonly, --linebreak--, pattern, maxlength, --linebreak--, min, max, step',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base, 
        --palette--;;detail,
	--div--;Advanced,
        --palette--;;layout,
       	--palette--;;attributes, 
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
			'showitem' => $showItem,
		],
	],
];

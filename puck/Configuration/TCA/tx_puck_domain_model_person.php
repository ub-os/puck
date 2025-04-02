<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\TcaUtility;

$ctrl = [
	'label' => 'name',
	'title' => 'Person',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/Person.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'name',
];
$interface = [
];
$columns = [
	'name' => [
		'label' => 'Name',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
			'required' => true,
		],
	],
	'slug' => [
		'label' => 'URL Segment',
		'config' => [
			'type' => 'slug',
			'generatorOptions' => [
				'fields' => ['name'],
				'fieldSeparator' => '-',
				'prefixParentPageSlug' => false,
				'replacements' => [
					'/' => '',
				],
			],
			'fallbackCharacter' => '-',
			'eval' => 'uniqueInSite',
			'default' => '',
		],
	],
	'description' => [
		'label' => 'Description',
		'config' => $GLOBALS['TCA']['pages']['columns']['abstract']['config'],
	],
	'position' => [
		'label' => 'Position',
		'displayCond' => 'FIELD:is_team_member:REQ:true',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],

	],
	'email' => [
		'label' => 'Email',
		'config' => [
			'type' => 'email',
			'eval' => 'trim',
			'max' => 255,
		]
	],
	'phone' => [
		'label' => 'Phone',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'is_team_member' => [
		'label' => 'Team member',
		'onChange' => 'reload',
		'config' => [
			'type' => 'check',
			'default' => 1
		],
	],
	'pages' => [
		'label' => 'Page',
		'config' => [
			'type' => 'group',
			'allowed' => 'pages',
			'foreign_table' => 'pages',
			'MM' => 'tx_puck_person_page_mm',
			'size' => 1,
			'maxitems' => 1
		],
	],
	'link' => [
		'label' => 'Link',
		'config' => [
			'type' => 'link',
		]
	],
	'link_linkedin' => [
		'label' => 'Link Linkedin',
		'config' => [
			'type' => 'link',
		]
	],
	'link_xing' => [
		'label' => 'Link Xing',
		'config' => [
			'type' => 'link',
		]
	],
	'assets' => [
		'label' => 'Media',
		'config' => $GLOBALS['TCA']['tt_content']['columns']['assets']['config'],
	],
	'sys_language_uid' => [
		'exclude' => true,
		'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
		'config' => [
			'type' => 'language',
		],
	],
	'l10n_parent' => [
		'displayCond' => 'FIELD:sys_language_uid:>:0',
		'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				['', 0],
			],
			'foreign_table' => 'tx_puck_domain_model_person',
			'foreign_table_where' => 'AND {#tx_puck_domain_model_person}.{#pid}=###CURRENT_PID### AND {#tx_puck_domain_model_person}.{#sys_language_uid} IN (-1,0)',
			'default' => 0,
		],
	],
	'l10n_diffsource' => [
		'config' => [
			'type' => 'passthrough',
		],
	],
	//'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
];
$columns['assets']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = TcaUtility::getCropVariants('1:1,4:3,3:2,2:1');

$palettes = [
	'person' => [
		'label' => 'Person',
		'showitem' => '
       name, is_team_member,
       --linebreak--,
       slug, 
       --linebreak--,
       position,
       --linebreak--,
       description,
    --linebreak--,
       pages,'
	],
	'contact' => [
		'label' => 'Contact',
		'showitem' => '
        email, phone,
        --linebreak--,
        link, link_linkedin, link_xing'
	]
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;person,
        --palette--;;contact,    
    --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
        assets,     
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

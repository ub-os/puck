<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ctrl = [
    'label' => 'name',
    'title' => 'Author',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'origUid' => 't3_origuid',
    'sortby' => 'sorting',
    'delete' => 'deleted',
    'versioningWS' => true,
    'languageField' => 'sys_language_uid',
    'transOrigPointerField' => 'l10n_parent',
    'transOrigDiffSourceField' => 'l10n_diffsource',
    'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/Author.svg',
    'enablecolumns' => [
        'disabled' => 'hidden',
    ],
    'searchFields' => 'name',
];
$interface = [
    'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, position, description',
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
    'email' => [
        'label' => 'Email',
        'config' => [
            'type' => 'input',
            'eval' => 'trim,email',
            'max' => 255,
        ]
    ],
    'position' => [
        'label' => 'Position',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
        ],
    ],
    'link' => [
        'label' => 'Link',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
        ]
    ],
    'link_linkedin' => [
        'label' => 'Link Linkedin',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
        ]
    ],
    'link_xing' => [
        'label' => 'Link Xing',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
        ]
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
            'foreign_table' => 'tx_puck_domain_model_author',
            'foreign_table_where' => 'AND {#tx_puck_domain_model_author}.{#pid}=###CURRENT_PID### AND {#tx_puck_domain_model_author}.{#sys_language_uid} IN (-1,0)',
            'default' => 0,
        ],
    ],
    'l10n_diffsource' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        name,
        slug, 
        description,
        email,
        position,
        link,
        link_linkedin,
        link_xing, 
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
    'types' => [
        '0' => [
            'showitem' => $showItem,
        ],
    ],
];

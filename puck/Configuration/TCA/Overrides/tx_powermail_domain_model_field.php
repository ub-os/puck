<?php


$GLOBALS['TCA']['tx_powermail_domain_model_field']['types']['navigation']['showitem'] = '
    page, 
    title,
    type, 
--div--;LLL:EXT:powermail/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_field.sheet1, 
    --palette--;Layout;43, 
    --palette--;LLL:EXT:powermail/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_field.marker_title;5,
--div--;LLL:EXT:powermail/Resources/Private/Language/locallang_db.xlf:tabs.access, 
    sys_language_uid, 
    l10n_parent, 
    l10n_diffsource, 
    hidden, 
    starttime, 
    endtime';

$GLOBALS['TCA']['tx_powermail_domain_model_field']['types']['navigation']['columnsOverrides'] = [
    'title' => [
        'label' => 'Button labels',
        'description' => 'Labels for the buttons to navigate through the form steps, separated by "|", e.g.: Previous | Next',
    ]
];
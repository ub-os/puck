<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

require_once ExtensionManagementUtility::extPath('theme').'/Configuration/_Content/ContentTCA.php';

$baseShowItem = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
        --palette--;;language,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
        --palette--;;hidden,
        --palette--;;access,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
        rowDescription,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,';

foreach(glob(ExtensionManagementUtility::extPath('theme').'Classes/Domain/Model/Content/*.php') as $contentFile) {
    $namespace = 'UBOS\\Theme\\Domain\\Model\\Content\\';
    $className = str_replace('.php', '', basename($contentFile));
    $fullClassName = $namespace.$className;
    $cTypeName = 'theme_'.ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $className)), '_');
    $class = new $fullClassName;
    $GLOBALS['TCA']['tt_content']['types'][$cTypeName]['showitem'] = $class->showItem().$baseShowItem;
    $GLOBALS['TCA']['tt_content']['types'][$cTypeName]['columnsOverrides'] = $class->columnsOverrides();

    ExtensionManagementUtility::addTypoScript('theme', 'setup',
        'tt_content.'.$cTypeName.' {
    20.view {
        templateRootPaths.100 = EXT:theme/Resources/Private/Fluid/
        layoutRootPaths.100 = EXT:theme/Resources/Private/Fluid/
        partialRootPaths.100 = EXT:theme/Resources/Private/Fluid/
    }
}');
}


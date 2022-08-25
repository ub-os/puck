<?php
if (!defined('TYPO3_MODE')) {
    die ('Acess denied.');
}
// Register tsconfig
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:theme/Configuration/TSconfig/Page.tsconfig">'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:theme/Configuration/TSconfig/User.tsconfig">'
);

// Register RTE configuration file
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['theme'] = 'EXT:theme/Configuration/RTE/Theme.yaml';
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['header'] = 'EXT:theme/Configuration/RTE/Header.yaml';

// Register icons Registry
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme') .'/Configuration/_Content/IconRegistry.php';

// Register PageLayoutView REMOVED

// Define fluid_components Namespaces
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = [
    'UBOS\\Theme\\Layouts' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme', 'Resources/Private/FluidComponents/Layouts'),
    'UBOS\\Theme\\Elements' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme', 'Resources/Private/FluidComponents/Elements'),
    'UBOS\\Theme\\Modules' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme', 'Resources/Private/FluidComponents/Modules'),
    'UBOS\\Theme\\Icons' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme', 'Resources/Private/FluidComponents/Icons')
];

// Add Global Fluid Namespaces
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['layout'] = ['UBOS\Theme\Layouts'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['element'] = ['UBOS\Theme\Elements'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['module'] = ['UBOS\Theme\Modules'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['icon'] = ['UBOS\Theme\Icons'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['ubos'] = ['UBOS\Theme\ViewHelpers'];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['i'] = ['B13\Picture\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['v'] = ['FluidTYPO3\Vhs\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['n'] = ['GeorgRinger\News\ViewHelpers'];

// Set css and js gzip compression
$GLOBALS['TYPO3_CONF_VARS']['BE']['compressionLevel'] = 9;
$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] = 9;

// Set Allowed Media File Extensions
$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'gif,jpg,jpeg,bmp,png,svg,webp';

// Backend Extension Configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendLogo'] = 'EXT:theme/Resources/Public/images/favicons/favicon-32x32.png';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendFavicon'] = 'EXT:theme/Resources/Public/images/favicons/favicon.ico';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginHighlightColor'] = '#8CBE28';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginLogo'] = 'EXT:theme/Resources/Public/images/icons/website-logo.svg';

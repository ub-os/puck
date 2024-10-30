<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puckloader\Loader;

Loader::loadConf('puck');

foreach (glob(ExtensionManagementUtility::extPath('puck') . 'Configuration/ContentElements/*.php') as $file) {
    (include $file)?->addTypoScript();
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedAliasMapperOfCommaList'] = \UBOS\Puck\Routing\Aspect\PersistedAliasMapperOfCommaList::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['NothingMapper'] = \UBOS\Puck\Routing\Aspect\NothingMapper::class;

// Register RTE configuration file
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['puck_default'] = 'EXT:puck/Configuration/RTE/Default.yaml';
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['puck_header'] = 'EXT:puck/Configuration/RTE/Header.yaml';

// Define fluid_components Namespaces
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces'] = [
    'UBOS\\Puck\\Layouts' => ExtensionManagementUtility::extPath('puck', 'Resources/Private/FluidComponents/Layouts'),
    'UBOS\\Puck\\Elements' => ExtensionManagementUtility::extPath('puck', 'Resources/Private/FluidComponents/Elements'),
    'UBOS\\Puck\\Modules' => ExtensionManagementUtility::extPath('puck', 'Resources/Private/FluidComponents/Modules'),
    'UBOS\\Puck\\Icons' => ExtensionManagementUtility::extPath('puck', 'Resources/Private/FluidComponents/Icons')
];

// Add Global Fluid Namespaces
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['layout'] = ['UBOS\Puck\Layouts'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['element'] = ['UBOS\Puck\Elements'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['module'] = ['UBOS\Puck\Modules'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['icon'] = ['UBOS\Puck\Icons'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['puck'] = ['UBOS\Puck\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['u'] = ['UBOS\Puck\ViewHelpers'];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['v'] = ['FluidTYPO3\Vhs\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['n'] = ['GeorgRinger\News\ViewHelpers'];

// Set css and js gzip compression
$GLOBALS['TYPO3_CONF_VARS']['BE']['compressionLevel'] = 9;
$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] = 9;

// Backend Extension Configuration
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['puck'] = 'EXT:puck/Resources/Public/Css/dist/puck-backend.min.css';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendLogo'] = 'EXT:puck/Resources/Public/Icons/Favicons/packages/default/android-chrome-72x72.png';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendFavicon'] = 'EXT:puck/Resources/Public/Icons/Favicons/packages/default/favicon.ico';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginHighlightColor'] = '#3a3d3a';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginLogo'] = 'EXT:puck/Resources/Public/Icons/Logos/default.svg';


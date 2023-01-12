<?php
if (!defined('TYPO3_MODE')) {
    die ('Acess denied.');
}
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use HDNET\Autoloader\Loader;
use UBOS\Puck\Utility\PuckUtility;
use UBOS\Puck\Controller\PageController;
use UBOS\Puck\Controller\ContentController;

ExtensionUtility::configurePlugin(
    'Puck',
    'Page',
    [PageController::class => 'index'],
);
ExtensionUtility::configurePlugin(
    'Puck',
    'Content',
    [ContentController::class => 'index'],
);
// Register tsconfig
ExtensionManagementUtility::addPageTSConfig(
    "@import 'EXT:puck/Configuration/TSconfig/Page.tsconfig'
    @import 'EXT:puck/Configuration/TSconfig/Mod.tsconfig'"
);
ExtensionManagementUtility::addUserTSConfig(
    "@import 'EXT:puck/Configuration/TSconfig/User.tsconfig'"
);
require_once ExtensionManagementUtility::extPath('puck') . '/Configuration/IconRegistry.php';

Loader::extLocalconf('UBOS', 'puck', array('ContentObjects', 'SmartObjects', 'Plugins'));

$contentModels = PuckUtility::indexContentModels();
foreach($contentModels as $model) {
    ExtensionManagementUtility::addTypoScript(
        'puck',
        'setup',
        'tt_content.'.$model['typeKey'].'.20  {
                userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
                extensionName = Puck
                pluginName = Content
                vendorName = UBOS
                settings {
                    contentElement = '.$model['name'].'
                    extensionKey = puck
                    vendorName = UBOS
                }   
        }',
        'defaultContentRendering'
    );
}

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
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['ub'] = ['UBOS\Puck\ViewHelpers'];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['v'] = ['FluidTYPO3\Vhs\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['n'] = ['GeorgRinger\News\ViewHelpers'];

// Set css and js gzip compression
$GLOBALS['TYPO3_CONF_VARS']['BE']['compressionLevel'] = 9;
$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] = 9;

// Set Allowed Media File Extensions
$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] = 'gif,jpg,jpeg,bmp,png,svg,webp';

// Backend Extension Configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendLogo'] = 'EXT:puck/Resources/Public/Icons/Favicons/android-chrome-72x72.png';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendFavicon'] = 'EXT:puck/Resources/Public/Icons/Favicons/favicon.ico';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginHighlightColor'] = '#A46CDC';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginLogo'] = 'EXT:puck/Resources/Public/Icons/website-logo.svg';


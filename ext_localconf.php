<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility as ExtUtil;
use UBOS\Puck\Attribute\AttributeReflection;
use UBOS\Puck\Configuration\ContentElementConfiguration;

// Extbase plugins (from #[AsAction] attributes) and content element rendering
// TypoScript (one config file per element). Both resolve declarative definitions
// via reflection / file scans and cache the result themselves (see BootCache),
// so this stays cheap even though ext_localconf.php runs on every request.
AttributeReflection::configurePlugins('puck', 'Classes/Controller', 'UBOS\Puck\Controller');
ContentElementConfiguration::registerAll('Configuration/ContentElements/*.php');

ExtUtil::addTypoScript(
	'puck',
	'setup',
	'contentFragmentPage.tt_content < tt_content',
	'defaultContentRendering'
);

// Register RTE configuration file
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['puck_default'] = 'EXT:puck/Configuration/RTE/Default.yaml';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedAliasListMapper'] = \UBOS\Puck\Routing\Aspect\PersistedAliasListMapper::class;

// Add Global Fluid Namespaces
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['layout'] = ['UBOS\Puck\Components\Layouts'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['element'] = ['UBOS\Puck\Components\Elements'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['module'] = ['UBOS\Puck\Components\Modules'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['icon'] = ['UBOS\Puck\Components\Icons'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['puck'] = ['UBOS\Puck\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['u'] = ['UBOS\Puck\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['tag'][] = \UBOS\Puck\ViewHelpers\TagComponentProvider::class;

// Set css and js gzip compression
$GLOBALS['TYPO3_CONF_VARS']['BE']['compressionLevel'] = 9;
$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] = 9;

// Backend Extension Configuration
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['puck'] = 'EXT:puck/Resources/Public/Css/dist/puck-backend.min.css';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendLogo'] = 'EXT:puck/Resources/Public/Favicons/packages/default/android-chrome-192x192.png';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['backendFavicon'] = 'EXT:puck/Resources/Public/Favicons/packages/default/favicon.ico';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginHighlightColor'] = '#ff8700';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['backend']['loginLogo'] = 'EXT:puck/Resources/Public/Images/Logos/default.svg';

 <?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use HDNET\Autoloader\Loader;
use UBOS\Theme\Utility\ThemeUtility;

$languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

ExtensionManagementUtility::allowTableOnStandardPages('tx_theme_domain_model_inline_media');

// IS SET IN SYS_TEMPLATES.PHP
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('theme', 'Configuration/TypoScript', 'theme');
//require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme') .'/Configuration/_Content/_CTypes/CTypesIconClasses.php';

$GLOBALS['TBE_STYLES']['skins']['theme']['name'] = 'Theme';
$GLOBALS['TBE_STYLES']['skins']['theme']['stylesheetDirectories']['css'] = 'EXT:theme/Resources/Public/css/backend/';

$contentModels = ThemeUtility::indexContentModels();
foreach($contentModels as $model) {
    $GLOBALS['TCA']['tt_content']['types'][$model['typeKey']]['noAutoloaderOverride'] = true;
}

Loader::extTables('UBOS', 'theme', array('ContentObjects', 'SmartObjects', 'Plugins'));

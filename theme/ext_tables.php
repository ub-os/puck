 <?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_theme_domain_model_inline_textmedia');

// IS SET IN SYS_TEMPLATES.PHP
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('theme', 'Configuration/TypoScript', 'theme');

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme') .'/Configuration/_Content/_CTypes/CTypesIconClasses.php';

$GLOBALS['TBE_STYLES']['skins']['theme']['name'] = 'Theme';
$GLOBALS['TBE_STYLES']['skins']['theme']['stylesheetDirectories']['css'] = 'EXT:theme/Resources/Public/css/backend/';

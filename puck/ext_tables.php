 <?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use HDNET\Autoloader\Loader;
use UBOS\Puck\Loader\SmartContentObjectLoader;
 use UBOS\Puck\Domain\Repository\Page\PageRepository;


SmartContentObjectLoader::addTypesTSconfig();

$languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

 // Add new page type:
 $GLOBALS['PAGES_TYPES'][PageRepository::DOKTYPE_POST] = [
     'type' => 'web',
     'allowedTables' => '*',
 ];
// IS SET IN SYS_TEMPLATES.PHP
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('puck', 'Configuration/TypoScript', 'puck');
//require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('puck') .'/Configuration/_Content/_CTypes/CTypesIconClasses.php';

$GLOBALS['TBE_STYLES']['skins']['puck']['name'] = 'Puck';
$GLOBALS['TBE_STYLES']['skins']['puck']['stylesheetDirectories']['css'] = 'EXT:puck/Resources/Public/css/backend/';

$contentModels = SmartContentObjectLoader::indexModels();
foreach($contentModels as $model) {
    $GLOBALS['TCA']['tt_content']['types'][$model['typeKey']]['noAutoloaderOverride'] = true;
}

Loader::extTables('UBOS', 'puck', array('SmartObjects', 'Plugins'));

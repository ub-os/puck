 <?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puckloader\Loader;
use UBOS\Puck\Domain\Repository\Page\PageRepository;

$languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

 $GLOBALS['PAGES_TYPES'][PageRepository::DOKTYPE_POST] = [
     'type' => 'web',
     'allowedTables' => '*',
 ];

$GLOBALS['TBE_STYLES']['skins']['puck']['name'] = 'Puck';
$GLOBALS['TBE_STYLES']['skins']['puck']['stylesheetDirectories']['css'] = 'EXT:puck/Resources/Public/css/backend/';

Loader::loadTables('puck');

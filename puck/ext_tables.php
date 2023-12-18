 <?php
 defined('TYPO3') or die();

 use UBOS\Puck\Domain\Repository\PageRepository;
 use UBOS\Puckloader\Loader;

 $languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

 $GLOBALS['PAGES_TYPES'][PageRepository::DOKTYPES['news']] = [
     'type' => 'web',
     'allowedTables' => '*',
 ];

$GLOBALS['TBE_STYLES']['skins']['puck']['name'] = 'Puck';
$GLOBALS['TBE_STYLES']['skins']['puck']['stylesheetDirectories']['css'] = 'EXT:puck/Resources/Public/Css/dist/backend/';

Loader::loadTables('puck');

 <?php
 defined('TYPO3') or die();

 use UBOS\Puck\Domain\Repository\PageRepository;
 use UBOS\Puckloader\Loader;

$languageFilePrefix = 'LLL:EXT:fluid_styled_content/Resources/Private/Language/Database.xlf:';
$frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

Loader::loadTables('puck');

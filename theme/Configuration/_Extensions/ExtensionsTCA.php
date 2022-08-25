<?php
$files = glob( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme').'/Configuration/_Extensions/*/*TCA.php' );
foreach ( $files as $file )
  require_once $file;

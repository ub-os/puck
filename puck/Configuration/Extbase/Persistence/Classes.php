<?php
use HDNET\Autoloader\Utility\ExtbasePersistenceUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

$autoloaderMapping = ExtbasePersistenceUtility::getClassMappingForExtension('puck');
$autoloaderMapping['UBOS\Puck\Domain\Model\Page']['properties']['lastUpdated']['fieldName'] = 'lastUpdated';
return $autoloaderMapping;

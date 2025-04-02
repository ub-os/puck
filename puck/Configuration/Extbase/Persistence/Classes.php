<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Attribute\AttributeReflection;

return AttributeReflection::createPersistenceMapping(
	'puck',
	ExtensionManagementUtility::extPath('puck', 'Classes/Domain/Model/'),
	'UBOS\\Puck\\Domain\\Model\\'
);

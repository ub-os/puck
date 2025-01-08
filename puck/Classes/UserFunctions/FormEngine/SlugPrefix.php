<?php

namespace UBOS\Puck\UserFunctions\FormEngine;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

class SlugPrefix
{
	public function getHash(array $parameters, TcaSlug $reference): string
	{
		return "#";
	}

}

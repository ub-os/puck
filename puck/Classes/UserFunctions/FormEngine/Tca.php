<?php

namespace UBOS\Puck\UserFunctions\FormEngine;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

/**
 * TCA User Functions
 */
class Tca
{
	public function getHash(array $parameters, TcaSlug $reference): string
	{
		return "#";
	}

}

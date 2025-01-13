<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puckloader\Attribute\Plugin;

class FormalConsentController extends ActionController
{
	#[Plugin("ApproveConsent")]
	public function approveAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}

	#[Plugin("DismissConsent")]
	public function dismissAction(): ResponseInterface
	{
		return $this->htmlResponse();
	}
}

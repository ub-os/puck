<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puck\Attribute\AsAction;

/**
 * Controller for the Content plugin.
 * Default plugin for content elements.
 */
class ContentController extends ActionController
{
	use ContentModuleControllerTrait;

	#[AsAction("Content")]
	public function indexAction(): ResponseInterface
	{
		$this->prepareContentView();
		return $this->htmlResponse(
			$this->renderFluidComponent()
		);
	}
}

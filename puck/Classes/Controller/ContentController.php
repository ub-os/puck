<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use UBOS\Puck\Attribute\AsPlugin;

/**
 * Controller for the Content plugin.
 * Default plugin for content elements.
 */
#[AsPlugin("Content")]
class ContentController extends ActionController
{
	use ContentModuleControllerTrait;

	public function indexAction(): ResponseInterface
	{
		$this->prepareContentView();
		return $this->htmlResponse(
			$this->renderFluidComponent()
		);
	}
}

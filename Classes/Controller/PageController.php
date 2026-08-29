<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\DataProcessing\PageContentFetchingProcessor;
use UBOS\Puck\Attribute\AsAction;

/**
 * Default Page Controller.
 */
class PageController extends ActionController
{
	public function __construct(
		protected RecordFactory                $recordFactory,
		protected Context                      $context,
		protected PageContentFetchingProcessor $pageContentFetchingProcessor,
	)
	{
	}

	#[AsAction("Page")]
	public function indexAction(): ResponseInterface
	{
		$contentObjectRenderer = $this->request->getAttribute('currentContentObject');
		$site = $this->request->getAttribute('site');
		$variables = [
			'data' => $contentObjectRenderer->data,
			'settings' => $this->settings,
			'record' => $this->recordFactory->createResolvedRecordFromDatabaseRow(
				'pages',
				$contentObjectRenderer->data
			),
			'context' => [
				'site' => $site,
				'language' => $site->getLanguageById($this->context->getPropertyFromAspect('language', 'id')),
				'backendUser' => $this->context->getPropertyFromAspect('backend.user', 'username'),
			]
		];
		$variables = $this->pageContentFetchingProcessor->process(
			$contentObjectRenderer,
			[],
			['as' => 'content'],
			$variables
		);
		$this->view->assignMultiple($variables);
		return $this->htmlResponse();
	}
}

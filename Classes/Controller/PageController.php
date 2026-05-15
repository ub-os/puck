<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\DataProcessing\PageContentFetchingProcessor;
use UBOS\Puck\Attribute\AsAction;

/**
 * Default Page Controller.
 */
class PageController extends ActionController
{
	public function __construct(
		protected RecordFactory        $recordFactory,
		protected PageRenderer         $pageRenderer,
		protected AssetCollector       $assetCollector,
		protected Context 			   $context,
		protected ViewFactoryInterface $viewFactory,
		protected PageContentFetchingProcessor $pageContentFetchingProcessor,
	)
	{
	}

	#[AsAction("Page")]
	public function indexAction(): ResponseInterface
	{
		$contentObjectRenderer = $this->request->getAttribute('currentContentObject');
		$site = $this->request->getAttribute('site');
		$siteSettings = $site->getSettings();
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
		$this->addAssetTags();
		$this->addFaviconTags($siteSettings);
		$this->view->assignMultiple($variables);
		return $this->htmlResponse();
	}


	/**
	 * Add the extension stylesheets and javascript files to the page.
	 */
	protected function addAssetTags(): void
	{
		$assetPath = 'EXT:puck/Resources/Public';
		$puckCSS = $assetPath . '/Css/dist/puck.min.css';
		$puckJS = $assetPath . '/JavaScript/dist/puck.min.js';
		if (Environment::getContext()->isDevelopment()) {
			$puckCSS = $assetPath . '/Css/dist/puck.css';
			$puckJS = $assetPath . '/JavaScript/dist/puck.js';
		}
		$this->assetCollector->addStyleSheet(
			'puck-css',
			$puckCSS,
			['data-hx-preserve' => '1', 'id' => 'head-puck-css'],
			['priority' => true]
		);
		$this->assetCollector->addJavaScript(
			'puck-js',
			$puckJS,
			['data-hx-preserve' => '1', 'id' => 'head-puck-js', 'defer' => 'defer'],
			['priority' => true]
		);
	}

	/**
	 * Add the favicon head tags based on favicon name configured in site settings.
	 */
	protected function addFaviconTags($siteSettings): void
	{
		$faviconPath = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName(
			'EXT:puck/Resources/Public/Icons/Favicons/packages/'
			. ($siteSettings->get('template.favicon') ?? 'default')
		));
		$view = $this->viewFactory->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Fluid/Page/Head'],
				request: $this->request,
			)
		);
		$view->assign('faviconPath', $faviconPath);
		$this->pageRenderer->addHeaderData($view->render('FaviconTags'));
	}
}

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
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use UBOS\Puck\Attribute\AsAction;

/**
 * Default Page Controller.
 */
class PageController extends ActionController
{
	public function __construct(
		protected ContentContentObject $contentContentObject,
		protected RecordFactory        $recordFactory,
		protected PageRenderer         $pageRenderer,
		protected AssetCollector       $assetCollector,
		protected Context 			   $context,
		protected ViewFactoryInterface $viewFactory
	)
	{
	}

	#[AsAction("Page")]
	public function indexAction(): ResponseInterface
	{
		$contentObjectRenderer = $this->request->getAttribute('currentContentObject');
		$site = $this->request->getAttribute('site');
		$siteSettings = $site->getSettings();
		$variables = [];
		$variables['settings'] = $this->settings;
		$variables['record'] = $this->recordFactory->createResolvedRecordFromDatabaseRow(
			'pages',
			$contentObjectRenderer->data
		);
		$variables['context'] = [
			'backendUser' => $this->context->getPropertyFromAspect('backend.user', 'username'),
			'site' => $site,
			'language' => $site->getLanguageById($this->context->getPropertyFromAspect('language', 'id')),
		];
		$variables['contentElements'] = $this->renderContentElementsByColPos(
			$contentObjectRenderer,
			[
				['colPos' => 1, 'slide' => 0],
				['colPos' => 3, 'slide' => -1],
				['colPos' => 9, 'slide' => 0]
			]
		);
		$this->addAssetTags();
		$this->addFaviconTags($siteSettings);
		$this->view->assignMultiple($variables);
		return $this->htmlResponse();
	}

	/**
	 * Render content elements by colPos.
	 */
	protected function renderContentElementsByColPos(ContentObjectRenderer $contentObjectRenderer, array $contentAreas): array
	{
		$this->contentContentObject->setRequest($this->request);
		$this->contentContentObject->setContentObjectRenderer($contentObjectRenderer);
		$result = [];
		foreach ($contentAreas as $area) {
			$result['colPos' . $area['colPos']] = $this->contentContentObject->render([
				'table' => 'tt_content',
				'select.' => [
					'pidInList' => $contentObjectRenderer->data['uid'],
					'where' => '{#colPos}=' . $area['colPos'],
					'orderBy' => 'sorting',
				],
				'slide' => $area['slide']
			]);
		}
		return $result;
	}

	/**
	 * Add the extension stylesheets and javascript files to the page.
	 */
	protected function addAssetTags(): void
	{
		if (isset($this->request->getHeaders()['hx-request'])) {
			// If this is an HTMX request, we do not add the assets.
			// the hash appended to asset file names can lead to them being included multiple times when htmx merges the head because the file names are not identical => we remove the assets from the head on htmx requests
			return;
		}
		$puckCSS = 'EXT:puck/Resources/Public/Css/dist/puck.min.css';
		$puckJS = 'EXT:puck/Resources/Public/JavaScript/dist/puck.min.js';
		$puckBodyJS = 'EXT:puck/Resources/Public/JavaScript/dist/puck-body.min.js';
		if (Environment::getContext()->isDevelopment()) {
			$puckCSS = 'EXT:puck/Resources/Public/Css/dist/puck.css';
			$puckJS = 'EXT:puck/Resources/Public/JavaScript/dist/puck.js';
			$puckBodyJS = 'EXT:puck/Resources/Public/JavaScript/dist/puck-body.js';
		}
		$this->assetCollector->addStyleSheet(
			'puck-css',
			$puckCSS,
			['data-hx-preserve' => '1'],
		);
		$this->assetCollector->addJavaScript(
			'puck-js',
			$puckJS,
			['defer' => 'defer', 'data-hx-preserve' => '1'],
			['priority' => true]
		);
		$this->assetCollector->addJavaScript(
			'puck-body-js',
			$puckBodyJS,
			['defer' => 'defer'],
			['priority' => false]
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

<?php

declare(strict_types=1);

namespace UBOS\Puck\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\Site;
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
		if ($site instanceof Site) {
			$this->addTrackingMarkup($site);
		}
		$this->view->assignMultiple($variables);
		return $this->htmlResponse();
	}

	/**
	 * Injects the administrator defined tracking / analytics markup from the site
	 * configuration ("Tracking & Security" tab) into <head> and before </body>.
	 *
	 * Inline <script>/<style> blocks are handed to the PageRenderer with the CSP
	 * flag set, so TYPO3 registers a content hash (or a nonce, depending on the
	 * site's csp.yaml "behavior"). Hashes are deterministic and survive the page
	 * cache, keeping responses fully cacheable by reverse proxies / CDNs.
	 * External <script src> and the remaining markup are added verbatim - their
	 * hosts need allow-listing via the "Content-Security-Policy rules" field.
	 */
	protected function addTrackingMarkup(Site $site): void
	{
		$config = $site->getConfiguration();
		$this->injectTrackingMarkup(trim((string)($config['puck_tracking_head_html'] ?? '')), false);
		$this->injectTrackingMarkup(trim((string)($config['puck_tracking_body_html'] ?? '')), true);
	}

	protected function injectTrackingMarkup(string $markup, bool $footer): void
	{
		if ($markup === '') {
			return;
		}

		$counter = 0;
		$placement = $footer ? 'body' : 'head';
		$collectInline = function (array $matches) use (&$counter, $footer, $placement): string {
			[$full, $tag, $attributes, $content] = $matches;
			// External resources stay verbatim - covered by CSP host allow-listing.
			if (preg_match('/\bsrc\s*=/i', $attributes)) {
				return $full;
			}
			// Non-executable <script> (e.g. type="application/ld+json") stays verbatim.
			if (
				$tag === 'script'
				&& preg_match('/\btype\s*=\s*["\']?\s*([^"\'\s>]+)/i', $attributes, $type)
				&& !in_array(strtolower($type[1]), ['text/javascript', 'application/javascript', 'module'], true)
			) {
				return $full;
			}
			if (trim($content) === '') {
				return '';
			}
			$id = 'puck-tracking-' . $placement . '-' . $counter++;
			if ($tag === 'script') {
				if ($footer) {
					$this->pageRenderer->addJsFooterInlineCode($id, $content, null, false, true);
				} else {
					$this->pageRenderer->addJsInlineCode($id, $content, null, false, true);
				}
			} else {
				$this->pageRenderer->addCssInlineBlock($id, $content, null, false, true);
			}
			return '';
		};

		$rest = preg_replace_callback(
			'#<(script|style)\b([^>]*)>(.*?)</\1\s*>#is',
			$collectInline,
			$markup
		);
		$rest = trim((string)($rest ?? $markup));
		if ($rest !== '') {
			if ($footer) {
				$this->pageRenderer->addFooterData($rest);
			} else {
				$this->pageRenderer->addHeaderData($rest);
			}
		}
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
			'EXT:puck/Resources/Public/Favicons/packages/'
			. ($siteSettings->get('template.favicon') ?? 'default')
		));
		$view = $this->viewFactory->create(
			new ViewFactoryData(
				templateRootPaths: ['EXT:puck/Resources/Private/Templates/Page/Head'],
				request: $this->request,
			)
		);
		$view->assign('faviconPath', $faviconPath);
		$this->pageRenderer->addHeaderData($view->render('FaviconTags'));
	}
}

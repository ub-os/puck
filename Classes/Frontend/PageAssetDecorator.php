<?php

declare(strict_types=1);

namespace UBOS\Puck\Frontend;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\Event\BeforeJavaScriptsRenderingEvent;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Adds puck's asset tags to the <head> of every rendered frontend page:
 *
 *  - the extension stylesheet + script
 *  - the favicon <link> tags (package selected via the "puck.favicon" site setting)
 *  - the administrator tracking / analytics markup from the "Tracking & Security" tab
 *    (inline <script>/<style> as CSP-hashed assets; <noscript>/pixels as raw markup)
 *
 * Hooked on BeforeJavaScriptsRenderingEvent, which fires inside
 * PageRenderer::renderFrontendPage() before assets, headerData and footerData are
 * assembled - so everything registered here lands in the (cacheable) page output.
 * The event fires up to four times per render; we act only on the first (inline +
 * priority) pass.
 *
 * Inline <script>/<style> in the tracking fields are registered as CSP assets so
 * TYPO3 collects a content hash (deterministic, survives the page cache). They
 * always render in <head> - "Body end markup" is meant for non-script HTML
 * (<noscript>, tracking pixels); a script pasted there is moved to the head.
 * PageRenderer's own inline-JS queue is already drained by the time this event
 * fires, which is why the AssetCollector API is used instead of
 * addJsInlineCode()/addJsFooterInlineCode().
 */
final class PageAssetDecorator
{
	private const FAVICON_TAGS = <<<'HTML'
		<link rel="icon" href="{path}/favicon.ico" sizes="32x32">
		<link rel="icon" href="{path}/icon.svg" type="image/svg+xml">
		<link rel="apple-touch-icon" href="{path}/apple-touch-icon.png">
		<link rel="manifest" href="{path}/manifest.webmanifest">
		<meta name="theme-color" content="#fff">
		HTML;

	public function __construct(
		private readonly PageRenderer $pageRenderer,
	) {}

	#[AsEventListener]
	public function __invoke(BeforeJavaScriptsRenderingEvent $event): void
	{
		// Run once per render, on the first (inline + priority) JavaScript pass.
		if (!$event->isInline() || !$event->isPriority()) {
			return;
		}
		$request = $GLOBALS['TYPO3_REQUEST'] ?? null;
		if (
			!$request instanceof ServerRequestInterface
			|| !ApplicationType::fromRequest($request)->isFrontend()
		) {
			return;
		}

		$this->addExtensionAssets($event->getAssetCollector());

		$site = $request->getAttribute('site');
		if (!$site instanceof Site) {
			return;
		}
		$this->addFavicon($site);
		$this->addTrackingMarkup($event->getAssetCollector(), $site);
	}

	private function addExtensionAssets(AssetCollector $assets): void
	{
		$path = 'EXT:puck/Resources/Public';
		$css = $path . '/Css/dist/puck.min.css';
		$js = $path . '/JavaScript/dist/puck.min.js';
		if (Environment::getContext()->isDevelopment()) {
			$css = $path . '/Css/dist/puck.css';
			$js = $path . '/JavaScript/dist/puck.js';
		}
		$assets->addStyleSheet(
			'puck-css',
			$css,
			['data-hx-preserve' => '1', 'id' => 'head-puck-css'],
			['priority' => true]
		);
		$assets->addJavaScript(
			'puck-js',
			$js,
			['data-hx-preserve' => '1', 'id' => 'head-puck-js', 'defer' => 'defer'],
			['priority' => true]
		);
	}

	private function addFavicon(Site $site): void
	{
		$package = (string)($site->getSettings()->get('puck.favicon') ?: 'default');
		$path = PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName(
			'EXT:puck/Resources/Public/Favicons/packages/' . $package
		));
		$this->pageRenderer->addHeaderData(
			str_replace('{path}', rtrim($path, '/'), self::FAVICON_TAGS)
		);
	}

	private function addTrackingMarkup(AssetCollector $assets, Site $site): void
	{
		$config = $site->getConfiguration();
		$counter = 0;
		$head = $this->registerInlineAssets($assets, trim((string)($config['puck_tracking_head_html'] ?? '')), $counter);
		$body = $this->registerInlineAssets($assets, trim((string)($config['puck_tracking_body_html'] ?? '')), $counter);

		if ($head !== '') {
			$this->pageRenderer->addHeaderData($head);
		}
		if ($body !== '') {
			$this->pageRenderer->addFooterData($body);
		}
	}

	/**
	 * Pulls executable inline <script>/<style> out of the markup and registers
	 * them as CSP-hashed assets (rendered in <head>). Returns the remaining
	 * markup (external scripts, <noscript>, pixels, JSON-LD, ...) verbatim.
	 */
	private function registerInlineAssets(AssetCollector $assets, string $markup, int &$counter): string
	{
		if ($markup === '') {
			return '';
		}

		$collect = function (array $matches) use ($assets, &$counter): string {
			[$full, $tag, $attributes, $content] = $matches;
			// External resource - keep verbatim, host is allow-listed via CSP rules.
			if (preg_match('/\bsrc\s*=/i', $attributes)) {
				return $full;
			}
			// Non-executable <script> (e.g. type="application/ld+json") - keep verbatim.
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
			$id = 'puck-tracking-' . $counter++;
			if ($tag === 'script') {
				$assets->addInlineJavaScript($id, $content, [], ['csp' => true]);
			} else {
				$assets->addInlineStyleSheet($id, $content, [], ['csp' => true]);
			}
			return '';
		};

		$rest = preg_replace_callback('#<(script|style)\b([^>]*)>(.*?)</\1\s*>#is', $collect, $markup);

		return trim((string)($rest ?? $markup));
	}
}

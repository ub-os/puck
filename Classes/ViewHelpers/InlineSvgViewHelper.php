<?php

declare(strict_types=1);

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Security\SvgSanitizer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders an SVG file inline.
 *
 * The file is sanitized (editor-uploaded SVGs are an XSS vector once inlined) and
 * normalised once per file revision; the result is stored in the persistent "hash"
 * cache keyed by the file's SHA-1, so the DOM parse only happens on the first
 * uncached render after the file changes.
 *
 * Element ids are isolated per occurrence so multiple copies of the same SVG on
 * one page do not collide (which would break gradients, masks, clip-paths, ...).
 */
class InlineSvgViewHelper extends AbstractViewHelper
{
	private const CACHE_PREFIX = 'puck-inlinesvg-';

	/** Placeholder for the per-occurrence id prefix in the cached markup. */
	private const ID_PLACEHOLDER = '%%ISVG_ID%%';
	protected $escapeOutput = false;

	public function initializeArguments(): void
	{
		$this->registerArgument('file', FileInterface::class, 'File reference', true);
		$this->registerArgument('class', 'string', 'class', false, '');
	}

	public function render(): string
	{
		/** @var FileInterface $file */
		$file = $this->arguments['file'];
		if ($file->getExtension() !== 'svg') {
			return '';
		}

		$svg = $this->getPreparedSvg($file);
		if ($svg === '') {
			return '';
		}

		// Give this occurrence its own id namespace.
		$svg = str_replace(
			self::ID_PLACEHOLDER,
			'isvg-' . substr(md5(uniqid('', true)), 0, 8) . '-',
			$svg
		);

		$class = (string)$this->arguments['class'];
		if ($class !== '') {
			$svg = preg_replace(
				'/(<svg\b[^>]*)>/',
				'$1 class="' . htmlspecialchars($class, ENT_QUOTES) . '">',
				$svg,
				1
			) ?? $svg;
		}

		return $svg;
	}

	/**
	 * Returns the sanitized, comment-stripped SVG markup with every id (and every
	 * reference to it) replaced by {@see self::ID_PLACEHOLDER}. Cached by file SHA-1.
	 */
	private function getPreparedSvg(FileInterface $file): string
	{
		try {
			$identity = $file->getSha1();
		} catch (\Throwable) {
			$identity = md5($file->getStorage()->getUid() . ':' . $file->getIdentifier());
		}

		$cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('hash');
		$cacheKey = self::CACHE_PREFIX . $identity;

		$cached = $cache->get($cacheKey);
		if (is_string($cached)) {
			return $cached;
		}

		$raw = (string)$file->getContents();
		$prepared = $raw === '' ? '' : $this->prepare($raw);

		$cache->set($cacheKey, $prepared);
		return $prepared;
	}

	private function prepare(string $svg): string
	{
		$svg = GeneralUtility::makeInstance(SvgSanitizer::class)->sanitizeContent($svg);
		if (trim($svg) === '') {
			return '';
		}

		$svg = preg_replace('/<\?xml.*?\?>/s', '', $svg) ?? $svg;
		$svg = preg_replace('/<!--.*?-->/s', '', $svg) ?? $svg;

		return $this->placeholderIds(trim($svg));
	}

	private function placeholderIds(string $svg): string
	{
		if (!preg_match_all('/\bid="([^"]+)"/', $svg, $matches)) {
			return $svg;
		}
		$replacements = [];
		foreach (array_unique($matches[1]) as $id) {
			$new = self::ID_PLACEHOLDER . $id;
			$replacements['id="' . $id . '"'] = 'id="' . $new . '"';
			$replacements['url(#' . $id . ')'] = 'url(#' . $new . ')';
			$replacements['href="#' . $id . '"'] = 'href="#' . $new . '"';
			$replacements["url('#" . $id . "')"] = "url('#" . $new . "')";
		}
		return strtr($svg, $replacements);
	}
}

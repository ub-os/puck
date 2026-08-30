<?php

declare(strict_types=1);

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders an SVG file inline, isolating its element ids so multiple copies of the
 * same SVG on one page do not collide (which would break gradients, masks, ...).
 */
class InlineSvgViewHelper extends AbstractViewHelper
{
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
		$class = (string)$this->arguments['class'];

		if ($file->getExtension() !== 'svg') {
			return '';
		}

		$svg = (string)$file->getContents();
		$svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
		$svg = preg_replace('/<!--.*?-->/s', '', $svg);
		$svg = self::isolateIds($svg);

		if ($class !== '') {
			$svg = preg_replace(
				'/(<svg\b[^>]*)>/',
				'$1 class="' . htmlspecialchars($class, ENT_QUOTES) . '">',
				$svg,
				1
			);
		}

		return $svg;
	}

	/**
	 * Rewrites every id to a page-unique value and updates all references to it
	 * (url(#id), href="#id", xlink:href="#id").
	 */
	protected static function isolateIds(string $svg): string
	{
		if (!preg_match_all('/\bid="([^"]+)"/', $svg, $matches)) {
			return $svg;
		}
		$prefix = 'isvg-' . substr(md5(uniqid('', true)), 0, 8) . '-';
		$replacements = [];
		foreach (array_unique($matches[1]) as $id) {
			$new = $prefix . $id;
			$replacements['id="' . $id . '"'] = 'id="' . $new . '"';
			$replacements['url(#' . $id . ')'] = 'url(#' . $new . ')';
			$replacements['href="#' . $id . '"'] = 'href="#' . $new . '"';
			$replacements["url('#" . $id . "')"] = "url('#" . $new . "')";
		}
		return strtr($svg, $replacements);
	}
}

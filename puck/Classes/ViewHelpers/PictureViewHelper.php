<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;
use TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper as UriImageViewHelper;

/**
 * Advanced picture element ViewHelper with multiple source support
 *
 * Creates HTML5 <picture> elements with support for modern image formats (WebP, AVIF),
 * responsive breakpoints, retina displays, and various other features.
 *
 * Example usage:
 * <u:picture image="{image}" webp="true" breakpointSources="true" />
 * <u:picture src="fileadmin/image.jpg" class="hero-image" retina="true" />
 * <u:picture image="{image}" backgroundImage="true" width="1200" />
 */
class PictureViewHelper extends AbstractViewHelper
{
	protected $escapeOutput = false;

	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('class', 'string', '', false, '');
		$this->registerArgument('className', 'string', '', false, 'e-media');
		$this->registerArgument('image', 'mixed', '', false, null);
		$this->registerArgument('src', 'string', '', false, '');
		$this->registerArgument('width', 'integer', '', false, 1920);
		$this->registerArgument('sources', 'array', '', false, []);
		$this->registerArgument('sourceMaxWidth', 'integer', '', false, 0);
		$this->registerArgument('cropVariant', 'string', '', false, 'default');
		$this->registerArgument('breakpointSources', 'boolean', '', false, false);
		$this->registerArgument('webp', 'boolean', '', false, false);
		$this->registerArgument('avif', 'boolean', '', false, false);
		$this->registerArgument('retina', 'boolean', '', false, false);
		$this->registerArgument('retinaOnly', 'boolean', '', false, false);
		$this->registerArgument('loading', 'string', '', false, 'lazy');
		$this->registerArgument('breakpoints', 'array', '', false, ['xs' => 540, 's' => 660, 'sm' => 780, 'm' => 900, 'ml' => 1040, 'l' => 1180, 'xl' => 1340]);
		$this->registerArgument('backgroundImage', 'boolean', '', false, false);
		$this->registerArgument('reserveHeight', 'string', '', false, '');
		$this->registerArgument('additionalAttributes', 'array', '', false, []);
	}

	/**
	 * Creates a ViewHelper instance with default and custom arguments
	 *
	 * @param string $className Name of the ViewHelper class to instantiate
	 * @param array $arguments Custom arguments to set on the ViewHelper
	 * @return mixed ViewHelper instance
	 */
	protected function createViewHelper(string $className, array $arguments = []): mixed
	{
		$helper = GeneralUtility::makeInstance($className);
		$argumentDefinition = $helper->prepareArguments();
		$defaultArguments = array_map(
			function ($definition) {
				return $definition->getDefaultValue();
			},
			$argumentDefinition);
		$helper->setArguments(array_merge($defaultArguments, $arguments));
		$helper->initialize();
		return $helper;
	}

	/**
	 * Renders the picture element with appropriate sources
	 *
	 * Can create:
	 * - Standard picture element with multiple sources
	 * - Background image div with inline style
	 * - Responsive images with breakpoint-specific crop variants
	 * - WebP and AVIF format alternatives with fallbacks
	 * - Retina (2x) resolution variants
	 *
	 * @return string HTML output of the picture element or empty string if no image
	 */
	public function render(): string
	{
		if ($this->arguments['image'] === null && $this->arguments['src'] === '') {
			return '';
		}
		if ($this->arguments['backgroundImage']) {
			$src = $this->createViewHelper(UriImageViewHelper::class, [
				'src' => $this->arguments['src'],
				'treatIdAsReference' => false,
				'image' => $this->arguments['image'],
				'cropVariant' => $this->arguments['cropVariant'],
				'width' => $this->arguments['width'],
				'absolute' => true,
			])->render();
			if ($this->arguments['webp']) {
				$src = $src . '.webp';
			}
			if ($this->arguments['avif']) {
				$src = $src . '.avif';
			}
			return "<div class=\"{$this->arguments['className']}__image {$this->arguments['class']}\" style=\"background-image: url('{$src}');\"></div>";
		}

		$pictureClass = "{$this->arguments['className']}__picture";
		$pictureStyle = "";
		if ($this->arguments['reserveHeight']) {
			$pictureClass .= " -reserveHeight";
			$pictureStyle = "padding-top: {$this->arguments['reserveHeight']};";
		}
		$pictureHtml = "<picture class=\"{$pictureClass}\" style=\"{$pictureStyle}\">";


		$title = '';
		$alt = '';
		if (method_exists($this->arguments['image'], 'getTitle')) {
			$title = $this->arguments['image']->getTitle() ?? '';
		}
		if (method_exists($this->arguments['image'], 'getAlternative')) {
			$alt = $this->arguments['image']->getAlternative() ?? '';
		}
		$imageArgs = [];
		if ($this->arguments['image']) {
			$imageArgs = [
				'image' => $this->arguments['image'],
				'width' => $this->arguments['width'],
				'absolute' => true,
				'cropVariant' => $this->arguments['cropVariant'],
				'treatIdAsReference' => false,
				'title' => $title,
				'alt' => $alt,
				'loading' => $this->arguments['loading'],
				'additionalAttributes' => array_merge($this->arguments['additionalAttributes'], ['loading' => $this->arguments['loading']]),
			];
		} else {
			$imageArgs = [
				'src' => $this->arguments['src'],
				'width' => $this->arguments['width'],
				'absolute' => true,
				'treatIdAsReference' => false,
				'loading' => $this->arguments['loading'],
				'additionalAttributes' => array_merge($this->arguments['additionalAttributes'], ['loading' => $this->arguments['loading']]),
			];
		}
		$imageHtml = str_replace(
			'<img ',
			"<img class='{$this->arguments['className']}__image {$this->arguments['class']}' ",
			$this->createViewHelper(ImageViewHelper::class, $imageArgs)->render()
		);
		if (!$this->arguments['image']) {
			return "{$pictureHtml}{$imageHtml}</picture>";
		}

		$sources = $this->arguments['sources'];
		if ($this->arguments['breakpointSources']) {
			$imageBreakpoints = explode(',', $this->arguments['image']->getProperties()['breakpoints']) ?? [];
			foreach ($imageBreakpoints as $breakpoint) {
				if (!$breakpoint || $breakpoint === 'default') {
					continue;
				}
				if (isset($sources[$breakpoint])) {
					$sources[$breakpoint]['cropVariant'] = $breakpoint;
					continue;
				}
				$width = $this->arguments['breakpoints'][$breakpoint] ?? (int)$breakpoint;
				if ($width > $this->arguments['width']) {
					$width = $this->arguments['width'];
				}
				$sources[$breakpoint] = [
					'cropVariant' => $breakpoint,
					'width' => $width,
				];
			}

		}
		$sourcesHtml = '';
		uksort($sources, fn($a, $b) => ($this->arguments['breakpoints'][$a] ?? (int)$a) - ($this->arguments['breakpoints'][$b] ?? (int)$b));
		$sources['default'] = [
			'cropVariant' => $this->arguments['cropVariant'],
			'width' => $this->arguments['width'],
			'isDefaultSource' => true,
		];
		foreach ($sources as $breakpoint => $source) {
			$width = $source['width'];
			if ($this->arguments['sourceMaxWidth'] > 0 && $width > $this->arguments['sourceMaxWidth']) {
				$width = $this->arguments['sourceMaxWidth'];
			}

			if ($this->arguments['retinaOnly'] && $this->arguments['retina']) {
				$srcset = '';
			} else {
				$srcset = $this->createViewHelper(UriImageViewHelper::class, [
					'image' => $this->arguments['image'],
					'cropVariant' => $source['cropVariant'] ?? '',
					'width' => $width,
					'absolute' => true,
					'treatIdAsReference' => false,
				])->render();
			}

			$srcsetx2 = '';
			$x2 = '';
			if ($this->arguments['retina']) {
				if ($srcset) {
					$srcsetx2 .= ', ';
				}
				$srcsetx2 .= $this->createViewHelper(UriImageViewHelper::class, [
					'image' => $this->arguments['image'],
					'cropVariant' => $source['cropVariant'] ?? '',
					'width' => $width * 2,
					'absolute' => true,
					'treatIdAsReference' => false,
				])->render();
				if (!$this->arguments['retinaOnly']) {
					$x2 = ' 2x';
				}
			}
			$maxWidth = $breakpoint;
			if (isset($this->arguments['breakpoints'][$breakpoint])) {
				$maxWidth = $this->arguments['breakpoints'][$breakpoint];
			}
			$media = "media=\"(max-width: {$maxWidth}px)\"";
			if ($source['isDefaultSource'] ?? false) {
				$media = '';
			}
			if ($this->arguments['avif']) {
				$sourcesHtml .= "<source srcset=\"{$srcset}.avif {$srcsetx2}.avif{$x2}\" {$media} type=\"image/avif\">";
			}
			if ($this->arguments['webp']) {
				$sourcesHtml .= "<source srcset=\"{$srcset}.webp {$srcsetx2}.webp{$x2}\" {$media} type=\"image/webp\">";
			}
			$sourcesHtml .= "<source srcset=\"{$srcset}{$srcsetx2}{$x2}\" {$media}>";
		}
		return "{$pictureHtml}{$sourcesHtml}{$imageHtml}</picture>";
	}
}

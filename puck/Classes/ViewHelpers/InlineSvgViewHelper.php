<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InlineSvgViewHelper extends AbstractViewHelper
{
	protected $escapeOutput = false;

	public function initializeArguments(): void
	{
		$this->registerArgument('file', FileInterface::class, 'File reference', true);
		$this->registerArgument('class', 'string', 'class', false, '');
	}

	public function render()
	{
		$file = $this->arguments['file'];
		$class = $this->arguments['class'];

		if ($file->getExtension() !== 'svg') {
			return '';
		}

		$svgContent = $file->getContents();

		// remove invalid html tags
		$svgContent = preg_replace('/<\?xml.*?\?>/', '', $svgContent);
		// remove comments
		$svgContent = preg_replace('/<!--.*?-->/s', '', $svgContent);
		// insulate ids
		$svgContent = preg_replace('/id="([^"]+)"/', 'id="' . uniqid('inline-svg-') . '"', $svgContent);
		// Add class attribute if provided
		if ($class) {
			$svgContent = preg_replace('/(<svg[^>]*)(>)/', '$1 class="' . htmlspecialchars($class) . '"$2', $svgContent);
		}

		return $svgContent;
	}
}
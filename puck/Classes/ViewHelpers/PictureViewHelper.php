<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;
use TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper as UriImageViewHelper;

class PictureViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('class', 'string', '', false, '');
        $this->registerArgument('className', 'string', '', false, 'e-media');
        $this->registerArgument('image', 'mixed', '', false, null);
        $this->registerArgument('src', 'string', '', false, '');
        $this->registerArgument('width', 'integer', '', false, 1920);
        $this->registerArgument('sources', 'array', '', false, []);
        $this->registerArgument('cropVariant', 'string', '', false, 'default');
        $this->registerArgument('breakpointSources', 'boolean', '', false, false);
        $this->registerArgument('webp', 'boolean', '', false, false);
        $this->registerArgument('avif', 'boolean', '', false, false);
        $this->registerArgument('retina', 'boolean', '', false, false);
        $this->registerArgument('loading', 'string', '', false, 'lazy');
        $this->registerArgument('breakpoints', 'array', '', false, ['xs' => 450, 's' => 625, 'sm' => 800, 'm' => 975, 'ml' => 1150, 'l' => 1325, 'xl' => 1500]);
        $this->registerArgument('backgroundImage', 'boolean', '', false, false);
        $this->registerArgument('reserveHeight', 'string', '', false, '');
        $this->registerArgument('additionalAttributes', 'array', '', false, []);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string
    {
        if ($arguments['image'] === null && $arguments['src'] === '') {
            return '';
        }
        if ($arguments['backgroundImage']) {
            $src = UriImageViewHelper::renderStatic([
                'src' => $arguments['src'],
                'treatIdAsReference' => false,
                'image' => $arguments['image'],
                'cropVariant' => $arguments['cropVariant'],
                'width' => $arguments['width'],
                'height' => '',
                'absolute' => true,
                'crop' => null,
                'fileExtension' => '',
                'height' => '',
                'minWidth' => '',
                'minHeight' => '',
                'maxWidth' => '',
                'maxHeight' => '',
            ], $renderChildrenClosure, $renderingContext);
            if ($arguments['webp']) {
                $src = $src . '.webp';
            }
            if ($arguments['avif']) {
                $src = $src . '.avif';
            }
            return "<div class=\"{$arguments['className']}__image {$arguments['class']}\" style=\"background-image: url('{$src}');\"></div>";
        }

        $pictureClass = "{$arguments['className']}__picture";
        $pictureStyle = "";
        if ($arguments['reserveHeight']) {
            $pictureClass .= " -reserve-height";
            $pictureStyle = "padding-top: {$arguments['reserveHeight']};";
        }
        $pictureHtml = "<picture class=\"{$pictureClass}\" style=\"{$pictureStyle}\">";

        $imageHtml = '';
        if ($arguments['image']) {
            DebugUtility::debug($arguments['image']);
            $imageHtml = ImageViewHelper::renderStatic([
                'image' => $arguments['image'],
                'class' => "{$arguments['className']}__image {$arguments['class']}",
                'width' => $arguments['width'],
                'absolute' => true,
                'cropVariant' => $arguments['cropVariant'],
                'treatIdAsReference' => false,
                'title' => $arguments['image']->getTitle() ?? '',
                'alt' => $arguments['image']->getAlternative() ?? '',
                'loading' => $arguments['loading'],
                'additionalAttributes' => $arguments['additionalAttributes'],
            ], $renderChildrenClosure, $renderingContext);
        } else {
            $imageHtml = ImageViewHelper::renderStatic([
                'src' => $arguments['src'],
                'class' => "{$arguments['className']}__image {$arguments['class']}",
                'width' => $arguments['width'],
                'absolute' => true,
                'treatIdAsReference' => false,
                'loading' => $arguments['loading'],
                'additionalAttributes' => $arguments['additionalAttributes'],
            ], $renderChildrenClosure, $renderingContext);
        }

        if (!$arguments['image']) {
            return "{$pictureHtml}{$imageHtml}</picture>";
        }

        $sources = $arguments['sources'];

        if ($arguments['breakpointSources']) {
            $imageBreakpoints = explode(',', $arguments['image']->getProperties()['breakpoints']) ?? [];
            foreach ($imageBreakpoints as $breakpoint) {
                if (!$breakpoint) {
                    continue;
                }
                if (isset($sources[$breakpoint])) {
                    $sources[$breakpoint]['cropVariant'] = $breakpoint;
                    continue;
                }
                $width = $arguments['breakpoints'][$breakpoint] ?? (int)$breakpoint;
                if ($width > $arguments['width']) {
                    $width = $arguments['width'];
                }
                $sources[$breakpoint] = [
                    'cropVariant' => $breakpoint,
                    'width' => $width,
                ];
            }

        }

        $sources['default'] = [
            'cropVariant' => $arguments['cropVariant'],
            'width' => $arguments['width']
        ];

        $sourcesHtml = '';
        foreach ($sources as $breakpoint => $source) {
            $srcset = UriImageViewHelper::renderStatic([
                'image' => $arguments['image'],
                'cropVariant' => $source['cropVariant'],
                'width' => $source['width'],
                'absolute' => true,
                'treatIdAsReference' => false,
                'src' => '',
                'crop' => null,
                'fileExtension' => '',
                'height' => '',
                'minWidth' => '',
                'minHeight' => '',
                'maxWidth' => '',
                'maxHeight' => '',
            ], $renderChildrenClosure, $renderingContext);
            $srcsetx2 = '';
            $x2 = '';
            if ($arguments['retina']) {
                $srcsetx2 .= ', ';
                $srcsetx2 .= UriImageViewHelper::renderStatic([
                    'image' => $arguments['image'],
                    'cropVariant' => $source['cropVariant'],
                    'width' => $source['width'] * 2,
                    'absolute' => true,
                    'treatIdAsReference' => false,
                    'src' => '',
                    'crop' => null,
                    'fileExtension' => '',
                    'height' => '',
                    'minWidth' => '',
                    'minHeight' => '',
                    'maxWidth' => '',
                    'maxHeight' => '',
                ], $renderChildrenClosure, $renderingContext);
                $x2 = ' 2x';
            }
            $maxWidth = $breakpoint;
            if (isset($arguments['breakpoints'][$breakpoint])) {
                $maxWidth = $arguments['breakpoints'][$breakpoint];
            }
            $media = "media=\"(max-width: {$maxWidth}px)\"";
            if ($breakpoint === 'default') {
                $media = '';
            }
            if ($arguments['avif']) {
                $sourcesHtml .= "<source srcset=\"{$srcset}.avif {$srcsetx2}.avif{$x2}\" {$media} type=\"image/avif\">";
            }
            if ($arguments['webp']) {
                $sourcesHtml .= "<source srcset=\"{$srcset}.webp {$srcsetx2}.webp{$x2}\" {$media} type=\"image/webp\">";
            }
            $sourcesHtml .= "<source srcset=\"{$srcset} {$srcsetx2}{$x2}\" {$media}>";
        }
        return "{$pictureHtml}{$sourcesHtml}{$imageHtml}</picture>";
    }
}

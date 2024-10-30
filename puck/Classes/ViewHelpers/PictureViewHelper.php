<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;
use TYPO3\CMS\Fluid\ViewHelpers\Uri\ImageViewHelper as UriImageViewHelper;

class PictureViewHelper extends AbstractViewHelper
{
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
        $this->registerArgument('loading', 'string', '', false, 'lazy');
        $this->registerArgument('breakpoints', 'array', '', false, ['xs' => 450, 's' => 625, 'sm' => 800, 'm' => 975, 'ml' => 1150, 'l' => 1325, 'xl' => 1500]);
        $this->registerArgument('backgroundImage', 'boolean', '', false, false);
        $this->registerArgument('reserveHeight', 'string', '', false, '');
        $this->registerArgument('additionalAttributes', 'array', '', false, []);
    }

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
            $pictureClass .= " -reserve-height";
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
        $imageHtml = str_replace('<img ', "<img class='{$this->arguments['className']}__image {$this->arguments['class']}' ", $this->createViewHelper(ImageViewHelper::class, $imageArgs)->render());
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

            $srcset = $this->createViewHelper(UriImageViewHelper::class, [
                'image' => $this->arguments['image'],
                'cropVariant' => $source['cropVariant'] ?? '',
                'width' => $width,
                'absolute' => true,
                'treatIdAsReference' => false,
            ])->render();

            $srcsetx2 = '';
            $x2 = '';
            if ($this->arguments['retina']) {
                $srcsetx2 .= ', ';
                $srcsetx2 .= $this->createViewHelper(UriImageViewHelper::class, [
                    'image' => $this->arguments['image'],
                    'cropVariant' => $source['cropVariant'] ?? '',
                    'width' => $width * 2,
                    'absolute' => true,
                    'treatIdAsReference' => false,
                ])->render();
                $x2 = ' 2x';
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
            $sourcesHtml .= "<source srcset=\"{$srcset} {$srcsetx2}{$x2}\" {$media}>";
        }
        return "{$pictureHtml}{$sourcesHtml}{$imageHtml}</picture>";
    }
}

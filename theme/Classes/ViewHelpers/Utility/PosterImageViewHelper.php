<?php

namespace UBOS\Theme\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;


class PosterImageViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('src', 'string', '');
    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $poster = $arguments['src'].'.poster.jpg';
        if (file_exists($poster)) {
            return $poster;
        }
        return null;
    }
}

<?php

namespace UBOS\Theme\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;


class CheckUrlResponseViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('url', 'string', '', false);
    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $url = $arguments['url'];
        return strpos(@get_headers($url)[0], '200');
    }
}

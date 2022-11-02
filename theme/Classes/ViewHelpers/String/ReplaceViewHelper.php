<?php

namespace UBOS\Theme\ViewHelpers\String;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;


class ReplaceViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('search', 'string', '', false);
        $this->registerArgument('replace', 'string', '', false);
        $this->registerArgument('subject', 'string', '', false);

    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        return str_replace($arguments['search'], $arguments['replace'], $arguments['subject']);
    }
}

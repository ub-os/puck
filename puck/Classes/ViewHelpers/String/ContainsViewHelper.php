<?php

namespace UBOS\Puck\ViewHelpers\String;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ContainsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('haystack', 'string', '', false);
        $this->registerArgument('needle', 'string', '', false);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): bool
    {
        return str_contains($arguments['haystack'], $arguments['needle']);
    }
}

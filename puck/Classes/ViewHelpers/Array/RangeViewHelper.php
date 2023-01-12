<?php
namespace UBOS\Puck\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class RangeViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('start', 'mixed', 0, false);
        $this->registerArgument('end', 'mixed', 10, false);
        $this->registerArgument('step', 'mixed', 1, false);

    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        return range($arguments['start'], $arguments['end'], $arguments['step']);
    }
}

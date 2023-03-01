<?php
namespace UBOS\Puck\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class UnsetViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('array', 'array', '');
        $this->registerArgument('key', 'string', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        unset($arguments['array'][$arguments['key']]);
        return $arguments['array'];
    }
}

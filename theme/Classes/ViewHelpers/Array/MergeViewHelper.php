<?php
namespace UBOS\Theme\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MergeViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('a', 'mixed', '', false);
        $this->registerArgument('b', 'mixed', '', false);
    }
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $a = $arguments['a'];
        $b = $arguments['b'];
        if (is_array($a)) {
            return array_merge( $a, $b );
        } else {
            foreach($b as $key => $value) {
                $a->$key = $value;
            }
            return $a;
        }
    }
}

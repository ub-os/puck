<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class RenderAttributesViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    protected $escapeOutput = false;
    public function initializeArguments()
    {
        $this->registerArgument('attributes', 'array', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $attributes = $renderChildrenClosure();
        $string = '';
        if ($attributes === null) {
            return $string;
        }
        foreach($attributes as $key => $value) {
            $string .= $key.'="'.$value.'" ';
        }
        return $string;
    }
}

<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class AttributesViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    protected $escapeOutput = false;
    public function initializeArguments()
    {
        $this->registerArgument('attributes', 'mixed', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $attributes = $renderChildrenClosure();
        if (is_string($attributes)) {
            return $attributes;
        }
        if (is_array($attributes)) {
            $string = '';
            foreach($attributes as $key => $value) {
                $string .= $key.'="'.$value.'" ';
            }
            return $string;
        }
        return '';
    }
}

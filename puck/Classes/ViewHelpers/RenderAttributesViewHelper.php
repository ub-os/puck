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
        $this->registerArgument('keyReplacements', 'array', '', false, ['__' => ':']);
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
            if ($arguments['keyReplacements']) {
                // replace keys (e.g. "data____foo" => "data:foo", since ":" is not allowed in fluid array keys)
                $key = str_replace(array_keys($arguments['keyReplacements']), array_values($arguments['keyReplacements']), $key);
            }
            $string .= $key.'="'.$value.'" ';
        }
        return $string;
    }
}

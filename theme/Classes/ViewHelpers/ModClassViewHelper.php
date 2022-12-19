<?php
namespace UBOS\Theme\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class ModClassViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    protected $escapeOutput = false;
    public function initializeArguments()
    {
        $this->registerArgument('mods', 'array', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $mods = $renderChildrenClosure();
        $class = '';
        if (is_array($mods)) {
            foreach($mods as $key => $value) {
                if ($value && $value !== 'default') {
                    if ($value === 1 || $value === true) {
                        $class .= ' -' . $key;
                    } else {
                        $class .= ' -' . $key . '-' . $value;
                    }
                }
            }
        }
        return $class;
    }
}

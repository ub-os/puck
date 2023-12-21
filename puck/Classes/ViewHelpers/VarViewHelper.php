<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class VarViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('value', 'mixed', '', false, null);
        $this->registerArgument('if', 'boolean', '', false, false);
        $this->registerArgument('then', 'mixed', '', false, null);
        $this->registerArgument('else', 'mixed', '', false, null);
        $this->registerArgument('get', 'mixed', '', false, null);
        $this->registerArgument('set', 'string', '', false, '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): mixed
    {
        $value = $renderChildrenClosure() ?? $arguments['value'];
        if ($arguments['if'] && $arguments['then'] !== null) {
            $value = $arguments['then'];
        } elseif ($arguments['else'] !== null) {
            $value = $arguments['else'];
        }
        if ($value !== null && $arguments['set']) {
            $renderingContext->getVariableProvider()->add($arguments['set'], $value);
        }
        if ($arguments['get'] === '') {
            return $value;
        }
        if ($arguments['get'] !== null) {
            return $arguments['get'];
        }
        return null;
    }
}

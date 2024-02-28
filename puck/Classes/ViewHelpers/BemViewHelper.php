<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class BemViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('block', 'string', '', false, '');
        $this->registerArgument('el', 'string', '', false, '');
        $this->registerArgument('mod', 'array', '', false, []);
        $this->registerArgument('raw', 'string', '', false, '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string
    {
        $block = $arguments['block'] ?:
            $renderingContext->getVariableProvider()->get('block') ?:
                $renderingContext->getVariableProvider()->get('name') ?:
                    '';
        return trim($block . ($arguments['el'] ? '__' . $arguments['el'] : '') . self::renderModifiers($arguments['mod']) . ' ' . $arguments['raw']);
    }

    protected static function renderModifiers(array $modifiers): string
    {
        $result = '';
        foreach ($modifiers as $key => $value) {
            if ($value && $value !== 'default') {
                if ($value === '1' || $value === 1 || $value === true) {
                    $result .= ' -' . $key;
                } else {
                    $result .= ' -' . $key . '-' . $value;
                }
            }
        }
        return $result;
    }
}

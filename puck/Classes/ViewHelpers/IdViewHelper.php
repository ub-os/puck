<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class IdViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    protected const PREFIX = 'x';
    protected static int $idx = 0;
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('suffix', 'string', '', false, '');
        $this->registerArgument('set', 'string', '', false, '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): ?string
    {
        $result = self::PREFIX . self::$idx++ . ($arguments['suffix'] ? '-' . $arguments['suffix'] : '');
        if ($arguments['set']) {
            $renderingContext->getVariableProvider()->add($arguments['set'], $result);
            return null;
        }
        return $result;
    }
}
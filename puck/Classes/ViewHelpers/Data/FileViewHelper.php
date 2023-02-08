<?php

namespace UBOS\Puck\ViewHelpers\Data;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

use UBOS\Puck\Utility\PuckUtility;

class FileViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('path', 'string', '', true);
        $this->registerArgument('delimiter', 'string', '', false);
    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $path = $arguments['path'];
        $delimiter = $arguments['delimiter'];
        return PuckUtility::getArrayFromFile(
            \TYPO3\CMS\Core\Core\Environment::getPublicPath() . $path,
            true,
            $delimiter
        );
    }
}

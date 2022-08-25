<?php


namespace UBOS\Theme\ViewHelpers\Utility\Backend;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class GetProcessedValueViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('col', 'string', '', true);
        $this->registerArgument('value', 'string', '', true);
    }
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return '<b>'.BackendUtility::getItemLabel($arguments['table'], $arguments['col']).'</b> '.
            BackendUtility::getProcessedValue($arguments['table'], $arguments['col'], $arguments['value']);
    }
}


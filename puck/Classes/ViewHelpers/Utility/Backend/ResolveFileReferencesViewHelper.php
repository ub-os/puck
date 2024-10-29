<?php


namespace UBOS\Puck\ViewHelpers\Utility\Backend;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ResolveFileReferencesViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments(): void
    {
        $this->registerArgument('row', 'array', '', true);
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('field', 'string', '', true);
    }
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return BackendUtility::resolveFileReferences($this->arguments['table'], $this->arguments['field'], $this->arguments['row']);
    }
}


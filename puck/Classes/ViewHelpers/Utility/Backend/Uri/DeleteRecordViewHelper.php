<?php


namespace UBOS\Puck\ViewHelpers\Utility\Backend\Uri;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class DeleteRecordViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('record', 'mixed', '', true);
        $this->registerArgument('table', 'string', '', true);
    }
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
       return static::getDeleteUrl($arguments['record'], $arguments['table']);
    }

    protected static function getDeleteUrl($record, $table): string
    {
        $params = '&cmd[' . $table . '][' . $record['uid'] . '][delete]=1';
        return BackendUtility::getLinkToDataHandlerAction($params);
    }
}


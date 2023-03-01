<?php


namespace UBOS\Puck\ViewHelpers\Utility\Backend\Uri;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ToggleRecordVisibilityViewHelper extends AbstractViewHelper
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
       return static::getVisibilityToggleUrl($arguments['record'], $arguments['table']);
    }

    protected static function getVisibilityToggleUrl($record, $table): string
    {
        $hiddenField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
        if ($record[$hiddenField]) {
            $value = 0;
        } else {
            $value = 1;
        }
        $params = '&data[' . $table . '][' . (($record['_ORIG_uid'] ?? false) ?: ($record['uid'] ?? 0))
            . '][' . $hiddenField . ']=' . $value;
        return BackendUtility::getLinkToDataHandlerAction($params);
    }
}


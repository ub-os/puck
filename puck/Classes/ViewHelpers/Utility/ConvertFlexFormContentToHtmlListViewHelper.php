<?php

namespace UBOS\Puck\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertFlexFormContentToHtmlListViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('flexFormString', 'string', '', false);
    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $flexFormString = $arguments['flexFormString'];
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);

        return self::printArrayList($flexFormService->convertFlexFormContentToArray($flexFormString)['settings']);
    }

    protected static function printArrayList($array)
    {
        $html = "<ul>";

        foreach($array as $k => $v) {
            if (is_array($v)) {
                $html .= "<li>" . $k . self::printArrayList($v) . "</li>";
                continue;
            }

            $html .= "<li>". $k .": ". $v . "</li>";
        }

        $html .= "</ul>";
        return $html;
    }

    protected static function printArrayTable($array)
    {
        $html = "<table>";

        $html .= "<tr>";
        foreach($array as $k => $v) {
            $html .= "<th>". $k . "</th>";
        }
        $html .= "</tr>";
        $html .= "<tr>";
        foreach($array as $k => $v) {
            if (is_array($v)) {
                $html .= "<td>" . self::printArrayTable($v) . "</td>";
                continue;
            }

            $html .= "<td>". $v . "</td>";
        }
        $html .= "</tr>";

        $html .= "</table>";
        return $html;
    }
}

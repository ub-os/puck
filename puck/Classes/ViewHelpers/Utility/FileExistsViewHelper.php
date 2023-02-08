<?php

namespace UBOS\Puck\ViewHelpers\Utility;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

use TYPO3\CMS\Core\Utility\DebugUtility;

class FileExistsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('src', 'string', '');
    }

    public static function renderStatic(

        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        /*        $posterPath = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") ."://{$_SERVER['HTTP_HOST']}/{$arguments['src']}.jpg";
                if (strpos(@get_headers($posterPath)[0], '200')) {
                    return "{$arguments['src']}.jpg";
                }*/
        if (file_exists($_SERVER['DOCUMENT_ROOT']."/{$arguments['src']}")) {
            return "{$arguments['src']}";
        }
        return false;
    }
}

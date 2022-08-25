<?php

namespace UBOS\Theme\ViewHelpers\Data;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
        $pathExplodeDot = explode('.', $path);
        $fileExt = end($pathExplodeDot);
        $contents = file_get_contents(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . $path);
        switch ($fileExt) {
            case 'json':
                $result = json_decode($contents);
                break;
            case 'csv':
                $csv = array_map('str_getcsv', file(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . $path));
                array_walk($csv, function(&$a) use ($csv) {
                    $a = array_combine($csv[0], $a);
                });
                array_shift($csv); # remove column header
                $result = $csv;
                break;
            case 'xml':
                $xml = simplexml_load_string($contents, "SimpleXMLElement", LIBXML_NOCDATA);
                $json = json_encode($xml);
                $result = json_decode($json,TRUE);
                break;
            default:
                if ($delimiter) {
                    $result = explode($delimiter, $contents);
                } else {
                    $result = $contents;
                }
        }
        return $result;
    }
}

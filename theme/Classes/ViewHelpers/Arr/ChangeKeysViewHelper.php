<?php


namespace UBOS\Theme\ViewHelpers\Arr;

use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ChangeKeysViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    public function initializeArguments()
    {
        $this->registerArgument('array', 'mixed', '', false);
        $this->registerArgument('arrayOfArrays', 'mixed', '', false);
        $this->registerArgument('map', 'array', '', true);
    }
    protected static function changeArrayKeys($array, $map)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($map[$key]) {
                $result[$map[$key]] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    protected static function changeObjectKeys($object, $map)
    {
        $array = (array) $object;
        $result = [];
        foreach ($array as $key => $value) {
            $key = str_replace(chr(0).'*'.chr(0), '', $key);
            if ($map[$key]) {
                $result[$map[$key]] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    protected static function changeKeys($item, $map)
    {
        if(gettype($item) === 'object') {
            return static::changeObjectKeys($item, $map);
        } else if(gettype($item) === 'array') {
            return static::changeArrayKeys($item, $map);
        } else {
            return [];
        }
    }
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $arrayOfArrays = (array) $arguments['arrayOfArrays'];
        $array = $arguments['array'];
        $map = $arguments['map'];
        //check for lazyObjectStorage
        if (method_exists($arguments['arrayOfArrays'], 'toArray')) {
            $arrayOfArrays = $arguments['arrayOfArrays']->toArray();
        } else {
            $arrayOfArrays = $arguments['arrayOfArrays'];
        }
        if ($arrayOfArrays) {
            if (gettype($arrayOfArrays) === 'array') {
                return array_map(
                    function($item) use ($map) { return static::changeKeys($item, $map); },
                    $arrayOfArrays
                );
            } else if (gettype($arrayOfArrays) === 'object') {
                return array_map(
                    function($item) use ($map) { return static::changeKeys($item, $map); },
                    use_object_vars($arrayOfArrays)
                );
            }
        } elseif ($array) {
            return static::changeKeys($array, $map);
        } else {
            return [];
        }
    }
}

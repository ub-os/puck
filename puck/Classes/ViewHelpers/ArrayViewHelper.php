<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('input', 'array', '', false, []);
        $this->registerArgument('bulk', 'bool', '', false, false);
        $this->registerArgument('changeKeys', 'array', '', false);
        $this->registerArgument('indexKey', 'string', '', false);
        $this->registerArgument('keys', 'bool', '', false);
        $this->registerArgument('slice', 'string', '', false);
        $this->registerArgument('push', 'mixed', '', false);
        $this->registerArgument('merge', 'array', '', false);
        $this->registerArgument('mergeRecursive', 'array', '', false);
        $this->registerArgument('range', 'string', '', false);
        $this->registerArgument('unset', 'string', '', false);
        $this->registerArgument('search', 'string', '', false);
        $this->registerArgument('filter', 'array', '', false);
        $this->registerArgument('inverseFilter', 'array', '', false);
        $this->registerArgument('implode', 'string', '', false);
        $this->registerArgument('if', 'boolean', '', false);
        $this->registerArgument('set', 'string', '', false, '');
        $this->registerArgument('operations', 'string', '', false, 'unset changeKeys merge mergeRecursive range indexKey keys push slice filter inverseFilter search implode');
    }


    public function render(): array|string|null
    {
        $input = $this->arguments['input'] ?: $this->renderChildren() ?? [];
        $result = ArrayViewHelper::process($input, $this->arguments);
        if ($this->arguments['set']) {
            $this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
            return null;
        }
        return $result;
    }

    protected static function process(array $input, array $arguments): array|string|null
    {
        if ($arguments['if'] !== null && !$arguments['if']) {
            return $input;
        }
        if ($arguments['bulk']) {
            return array_map(function($value) use ($arguments) {
                return self::doOperations($value, $arguments);
            }, $input);
        } else {
            return self::doOperations($input, $arguments);
        }
    }

    protected static function doOperations(array $array, array $arguments): array|string
    {
        $operations = explode(' ', $arguments['operations']);
        foreach ($operations as $operation) {
            if ($arguments[$operation] !== null) {
                $array = self::$operation($array, $arguments[$operation]);
            }
        }
        return $array;
    }

    protected static function changeKeys(array $array, array $changeKeys): array
    {
        foreach ($changeKeys as $oldKey => $newKey) {
            if (isset($array[$oldKey])) {
                $array[$newKey] = $array[$oldKey];
                unset($array[$oldKey]);
            }
        }
        return $array;
    }

    protected static function keys(array $array, $keys): array
    {
        if ($keys === true) {
            return array_keys($array);
        }
        return $array;
    }

    protected static function slice(array $array, string $slice): array
    {
        $slice = explode(' ', $slice);
        if (count($slice) === 1) {
            $array = array_slice($array, (int)$slice[0]);
        } else if (count($slice)) {
            $array = array_slice($array, (int)$slice[0], (int)$slice[1]);
        }
        return $array;
    }

    protected static function merge(array $array, array $merge): array
    {
        return array_merge($array, $merge);
    }

    protected static function mergeRecursive(array $array, array $mergeRecursive): array
    {
        return ArrayUtility::mergeRecursiveWithOverrule($array, $mergeRecursive);
    }

    protected static function range(array $array, string $range): array
    {
        $range = explode(' ', $range);
        if (count($range) === 1) {
            $array = range(0, (int)$range[0]);
        } else if (count($range) === 2){
            $array = range((int)$range[0], (int)$range[1]);
        } else if (count($range)) {
            $array = range((int)$range[0], (int)$range[1], (int)$range[2]);
        }
        return $array;
    }

    protected static function unset(array $array, string $unset): array
    {
        $unset = explode(' ', $unset);
        foreach ($unset as $key) {
            unset($array[$key]);
        }
        return $array;
    }

    protected static function implode(array $array, string $implode): string
    {
        return implode($implode, $array);
    }

    protected static function indexKey(array $array, string $key): array
    {
        if (!$key) {
            return $array;
        }
        $arr = [];
        foreach ($array as $value) {
            $arr[$value[$key]] = $value;
        }
        return $arr;
    }

    protected static function push(array $array, $push): array
    {
        $array[] = $push;
        return $array;
    }

    protected static function search(array $array, string $search): array
    {
        return array_search($search, $array);
    }

    protected static function filter(array $array, array $filter): array
    {
        $array = array_filter($array, function($value) use ($filter) {
            foreach ($filter as $key => $val) {
                if ($value[$key] !== $val) {
                    return false;
                }
            }
            return true;
        });
        return $array;
    }

    protected static function inverseFilter(array $array, array $filter): array
    {
        $array = array_filter($array, function($value) use ($filter) {
            foreach ($filter as $key => $val) {
                if ($value[$key] === $val) {
                    return false;
                }
            }
            return true;
        });
        return $array;
    }

}

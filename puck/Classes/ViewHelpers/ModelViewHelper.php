<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class ModelViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('data', 'array', '', false, []);
        $this->registerArgument('map', 'string', '', false, '');
        $this->registerArgument('raw', 'object', '', false, null);
        $this->registerArgument('bulk', 'bool', '', false, false);
        $this->registerArgument('set', 'string', '', false, '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): mixed
    {
        $data = $renderChildrenClosure() ?? $arguments['data'];
        if (!isset($data['uid'])) {
            $data['uid'] = 0;
        }
        $name = $arguments['map'];
        if (str_starts_with($name, '~')) {
            $name = 'UBOS\\Puck\\Domain\\Model\\' . substr($name, 1);
        }
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        if ($arguments['bulk']) {
            $result = $dataMapper->map($name, $data);
        } else {
            $result = $dataMapper->map($name, [$data])[0];
        }
        if ($arguments['set']) {
            $renderingContext->getVariableProvider()->add($arguments['set'], $result);
            return null;
        }
        return $result;
    }
}

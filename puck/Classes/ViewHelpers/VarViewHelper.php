<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class VarViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('value', 'mixed', '', false, null);
        $this->registerArgument('if', 'boolean', '', false, false);
        $this->registerArgument('then', 'mixed', '', false, null);
        $this->registerArgument('else', 'mixed', '', false, null);
        $this->registerArgument('get', 'mixed', '', false, null);
        $this->registerArgument('set', 'string', '', false, '');
    }

    public function render(): mixed
    {
        $value = $this->arguments['value'] ?: $this->renderChildren() ?? null;
        if ($this->arguments['if'] && $this->arguments['then'] !== null) {
            $value = $this->arguments['then'];
        } elseif ($this->arguments['else'] !== null) {
            $value = $this->arguments['else'];
        }
        if ($value !== null && $this->arguments['set']) {
            $this->renderingContext->getVariableProvider()->add($this->arguments['set'], $value);
        }
        if ($this->arguments['get'] === '') {
            return $value;
        }
        if ($this->arguments['get'] !== null) {
            return $this->arguments['get'];
        }
        return null;
    }
}

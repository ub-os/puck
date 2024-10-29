<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IdViewHelper extends AbstractViewHelper
{
    protected const PREFIX = 'x';
    protected static int $idx = 0;
    public function initializeArguments(): void
    {
        // name, type, description, required, default, escape
        $this->registerArgument('suffix', 'string', '', false, '');
        $this->registerArgument('set', 'string', '', false, '');
    }

    public function render(): ?string
    {
        $result = self::PREFIX . self::$idx++ . ($this->arguments['suffix'] ? '-' . $this->arguments['suffix'] : '');
        if ($this->arguments['set']) {
            $this->renderingContext->getVariableProvider()->add($this->arguments['set'], $result);
            return null;
        }
        return $result;
    }
}
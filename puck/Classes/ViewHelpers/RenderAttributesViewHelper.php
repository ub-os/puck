<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
class RenderAttributesViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    public function initializeArguments(): void
    {
        $this->registerArgument('attributes', 'array', '');
        $this->registerArgument('keyReplacements', 'array', '', false, ['__' => ':']);
    }

    public function render(): string
    {
        $attributes = $this->arguments['attributes'] ?: $this->renderChildren() ?? [];
        $string = '';
        if ($attributes === null) {
            return $string;
        }
        foreach($attributes as $key => $value) {
            if ($this->arguments['keyReplacements']) {
                // replace keys (e.g. "data____foo" => "data:foo", since ":" is not allowed in fluid array keys)
                $key = str_replace(array_keys($this->arguments['keyReplacements']), array_values($this->arguments['keyReplacements']), $key);
            }
            $string .= $key.'="'.$value.'" ';
        }
        return $string;
    }
}

<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;

class MathViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    public function initializeArguments(): void
    {
        $this->registerArgument('eval', 'string', '');
    }

    public function render() {
        $expression = $this->arguments['eval'] ?: $this->renderChildren() ?? '';
        return MathExpressionNode::evaluateExpression($this->renderingContext, $expression, []);
    }
}

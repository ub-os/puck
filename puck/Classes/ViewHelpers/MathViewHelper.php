<?php
namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;

class MathViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    protected $escapeOutput = false;
    public function initializeArguments(): void
    {
        $this->registerArgument('eval', 'string', '');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $expression = $renderChildrenClosure();
        return MathExpressionNode::evaluateExpression($renderingContext, $expression, []);
    }
}

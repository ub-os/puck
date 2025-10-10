<?php

namespace UBOS\Puck\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;

/**
 * ViewHelper for evaluating mathematical expressions in Fluid templates
 *
 * Allows for calculating results of mathematical expressions directly in templates.
 * Uses TYPO3's built-in math expression parser for calculations.
 *
 * Example usage:
 * <u:math eval="10 * 5" /> <!-- Outputs: 50 -->
 * <u:math>2 + 2</u:math> <!-- Outputs: 4 -->
 * <u:math eval="{item.price} * {item.quantity}" /> <!-- Calculates dynamic values -->
 */
class MathViewHelper extends AbstractViewHelper
{
	protected $escapeOutput = false;

	public function initializeArguments(): void
	{
		$this->registerArgument('eval', 'string', 'Mathematical expression to evaluate', false);
	}

	/**
	 * Evaluates a mathematical expression
	 *
	 * Takes the expression either from the 'eval' argument or from the ViewHelper content.
	 * Passes the expression to TYPO3's MathExpressionNode for evaluation.
	 *
	 * @return int|float Result of the mathematical expression
	 */
	public function render(): int|float
	{
		$expression = $this->arguments['eval'] ?: $this->renderChildren() ?? '';
		return MathExpressionNode::evaluateExpression($this->renderingContext, $expression, []);
	}
}
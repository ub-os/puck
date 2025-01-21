<?php

declare(strict_types=1);


namespace UBOS\Puck\Domain\Condition;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use UBOS\Puck\Domain\Condition\Functions\FormConditionFunctionsProvider;

class ConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            FormConditionFunctionsProvider::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraints\ExpressionLanguageProvider;
use Symfony\Component\Validator\Constraints\ExpressionValidator;

final class AugmentedExpressionValidator extends ExpressionValidator
{
    public function __construct(?ExpressionLanguage $expressionLanguage = null)
    {
        $expressionLanguage ??= new ExpressionLanguage();
        $expressionLanguage->registerProvider(new ExpressionLanguageProvider());
        $expressionLanguage->addFunction(new ExpressionFunction('clone', fn (string $object) => "(clone {$object})", fn (mixed $_, object $object) => clone $object));
        parent::__construct($expressionLanguage);
    }
}

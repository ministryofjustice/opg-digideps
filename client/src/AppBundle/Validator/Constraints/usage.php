<?php

/*use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Type;
use Acme\Validator\Constraints\Chain;

$constraint1 = new Chain([new Type('string'), new Date()]);

$constraint2 = new Chain([
    'constraints' => [new Type('string'), new Date()],
    'stopOnError' => true,
]);

// Symfony Validator Component v2.x has an issue with nested constraints,
// see https://github.com/symfony/Validator/blob/fc0650c1825c842f9dcc4819a2eaff9922a07e7c/ConstraintValidatorFactory.php#L48.
// If you plan to use nested `Chain` constraints, consider using the `NoCacheConstraintValidatorFactory` decorator.
// Here is a usage example for the Silex application:

$app->register(new Silex\Provider\ValidatorServiceProvider());

$app['validator.validator_factory'] = $app->share($app->extend('validator.validator_factory', function ($factory, $app) {
    return new Acme\Validator\NoCacheConstraintValidatorFactory($factory, [
        'Acme\\Validator\\Constraints\\ChainValidator',
    ]);
}));*/

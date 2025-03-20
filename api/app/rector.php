<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

return RectorConfig::configure()
    ->withPaths([
//        __DIR__ . '/config',
//        __DIR__ . '/public',
        __DIR__ . '/src/Controller/Ndr',
//        __DIR__ . '/tests',
    ])
    
    // uncomment to reach your current PHP version
//     ->withPhpSets()
//    ->withRules([
//        ClassPropertyAssignToConstructorPromotionRector::class,
//        ReadOnlyPropertyRector::class,
//    ])
    ->withConfiguredRule(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute(Route::class),
        new AnnotationToAttribute(Security::class),
    ])
    ->withTypeCoverageLevel(0);

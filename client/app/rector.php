<?php

declare(strict_types=1);

use App\Utils\Rector\RenameImportsRector;
use Rector\CodingStyle\Rector\Stmt\RemoveUselessAliasInUseStatementRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;

// Rector doesn't guarantee the order in which refactorings are applied: if one refactor depends on the output
// from a previous one, it probably won't work. For that reason, this script has to run in steps, so that later
// refactorings can be applied on top of previous ones (for example, convert annotations to attributes, then
// convert the attribute; this is needed for Sensio Security annotations, which first have to be translated to
// attributes, then converted from Sensio attributes to Symfony IsGranted aattributes).
// Run this with:
//   STEP=1 ./vendor/bin/rector process
// then run again with the next step, e.g. STEP=2, and so on.
$configBuilder = RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src/Controller/SettingsController.php',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    );

$step = intval(getenv('STEP'));
if ($step === 0) {
    $step = 1;
}

switch ($step) {
    case 1:
        $configBuilder
            ->withConfiguredRule(AnnotationToAttributeRector::class, [
                new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
                new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\Security'),
                new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\Template'),
            ]);
        break;

    case 2:
        $configBuilder
            ->withRules([
                SecurityAttributeToIsGrantedAttributeRector::class,
            ]);
        break;

    case 3:
        $configBuilder
            ->withImportNames(importShortClasses: false, removeUnusedImports: true);
        break;

    case 4:
        $configBuilder->withRules([
            RemoveUselessAliasInUseStatementRector::class,
        ]);
        break;

    case 5:
        $configBuilder->withConfiguredRule(RenameImportsRector::class, [
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template' => 'Symfony\Bridge\Twig\Attribute\Template'
        ]);
        break;
}

return $configBuilder;

// after this, manual steps (could probably automate with Rector if I wanted):
// - fix imports of built-ins (e.g. Throwable, DateTime to \Throwable, \DateTime)
// - remove screwy aliases on imports (e.g. FormDir)
// - optimise imports
// - sort parameters on attributes
// - replace Sensio\Bundle\FrameworkExtraBundle\Configuration\Template with Symfony\Bridge\Twig\Attribute\Template

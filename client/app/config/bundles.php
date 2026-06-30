<?php

declare(strict_types=1);

use JMS\SerializerBundle\JMSSerializerBundle;
use OPG\Digideps\Frontend\App;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    FrameworkBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    App::class => ['all' => true],
    JMSSerializerBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'dev_with_debug' => true, 'test' => true, 'local' => true],
    DebugBundle::class => ['dev' => true, 'dev_with_debug' => true, 'test' => true, 'local' => true],
    TwigExtraBundle::class => ['all' => true],
    TwigComponentBundle::class  => ['all' => true],
];

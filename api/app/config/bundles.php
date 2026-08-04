<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use OPG\Digideps\Backend\App;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

return [
    App::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true, 'local' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    FriendsOfBehatSymfonyExtensionBundle::class => ['dev' => true, 'test' => true, 'local' => true],
    JMSSerializerBundle::class => ['all' => true],
    StofDoctrineExtensionsBundle::class => ['all' => true],
    FrameworkBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
];

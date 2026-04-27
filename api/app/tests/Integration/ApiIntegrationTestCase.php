<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiIntegrationTestCase extends KernelTestCase
{
    use ApiTestTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::configureTest();
    }
}

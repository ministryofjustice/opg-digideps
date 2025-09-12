<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiTestCase extends KernelTestCase
{
    use ApiTestTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::configureTest();
    }
}

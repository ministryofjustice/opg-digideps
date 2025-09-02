<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\UserDeputyService;
use PHPUnit\Framework\TestCase;

class UserDeputyServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->sut = new UserDeputyService();
    }

    public function testAddMissingUserDeputyAssociations(): void
    {
        $expected = 0;

        $actual = $this->sut->addMissingUserDeputyAssociations();

        self::assertEquals($expected, $actual);
    }
}

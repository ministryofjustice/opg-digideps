<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Entity\UserResearch;

use OPG\Digideps\Frontend\Entity\UserResearch\ElectronicDeviceType;
use OPG\Digideps\Frontend\Entity\UserResearch\UserResearchResponse;
use PHPUnit\Framework\TestCase;

final class UserResearchResponseTest extends TestCase
{
    public function testElectronicDeviceTypeDefaultsToNull(): void
    {
        self::assertNull(new UserResearchResponse()->getElectronicDeviceType());
    }

    public function testElectronicDeviceTypeCanBeSet(): void
    {
        $response = new UserResearchResponse();
        $deviceType = new ElectronicDeviceType();

        self::assertSame($response, $response->setElectronicDeviceType($deviceType));
        self::assertSame($deviceType, $response->getElectronicDeviceType());
    }
}

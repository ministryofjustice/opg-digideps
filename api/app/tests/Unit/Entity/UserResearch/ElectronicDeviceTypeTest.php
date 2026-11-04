<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Entity\UserResearch;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use OPG\Digideps\Backend\Entity\UserResearch\ElectronicDeviceType;
use PHPUnit\Framework\TestCase;

final class ElectronicDeviceTypeTest extends TestCase
{
    #[DataProvider('propertyProvider')]
    #[Test]
    public function constructSetsPropertiesBasedOnFormResponses(
        array $formResponses,
        ?bool $expectedDesktopValue,
        ?bool $expectedLaptopValue,
        ?bool $expectedMobileValue,
        ?bool $expectedTabletValue
    ): void {
        $electronicDeviceType = new ElectronicDeviceType($formResponses);

        self::assertEquals($expectedDesktopValue, $electronicDeviceType->getDesktop());
        self::assertEquals($expectedLaptopValue, $electronicDeviceType->getLaptop());
        self::assertEquals($expectedMobileValue, $electronicDeviceType->getMobile());
        self::assertEquals($expectedTabletValue, $electronicDeviceType->getTablet());
    }

    public static function propertyProvider(): array
    {
        return [
            'all set' => [['desktop', 'laptop', 'mobile', 'tablet'], true, true, true, true],
            'desktop set' => [['desktop'], true, null, null, null],
            'laptop set' => [['laptop'], null, true, null, null],
            'mobile set' => [['mobile'], null, null, true, null],
            'tablet set' => [['tablet'], null, null, null, true],
        ];
    }

    #[Test]
    public function constructNoPropertiesThrowsException(): void
    {
        self::expectException(\RuntimeException::class);
        new ElectronicDeviceType([]);
    }
}

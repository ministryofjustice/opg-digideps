<?php

declare(strict_types=1);

use App\Enum\ConvertableImageTypes;
use PHPUnit\Framework\TestCase;

class ConvertableImageTypesTest extends TestCase
{
    /**
     * @test
     * @dataProvider imageTypeProvider
     */
    public function convertsTo(ConvertableImageTypes $enum, string $expectedFiletype)
    {
        self::assertEquals($expectedFiletype, $enum->convertsTo());
    }

    public function imageTypeProvider()
    {
        return [
            'HEIC' => [ConvertableImageTypes::HEIC, 'jpeg'],
            'JFIF' => [ConvertableImageTypes::JFIF, 'jpeg'],
        ];
    }
}

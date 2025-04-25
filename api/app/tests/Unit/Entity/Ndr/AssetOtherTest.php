<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Ndr;

use App\Entity\Ndr\Asset;
use App\Entity\Ndr\AssetOther;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AssetOtherTest extends TestCase
{
    /**
     * @dataProvider serialisationGroupProvider
     */
    public function testSerialisation(?string $group, array $expectedData): void
    {
        $a = new AssetOther();

        $reflectionClass = new ReflectionClass(Asset::class);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setValue($a, 1);
        
        $a->setValue('20.40');
        $a->setTitle('title');
        $a->setDescription('description');
        $a->setValuationDate(new DateTime('2025-01-20'));
        
        self::assertEquals($expectedData, json_decode(SerializationTestHelper::serialize($a, 'ndr-asset'), true));
    } 

    public function serialisationGroupProvider()
    {
        return [
            'No group' => [
                null,
                [
                    'id' => 1,
                    'value' => '20.40',
                    'type' => 'other',
                    'title' => 'title',
                    'description' => 'description',
                    'valuation_date' => '2025-01-20T00:00:00+00:00',
                ],
            ],
            'Group ndr-asset' => [
                'ndr-asset',
                [
                    'id' => 1,
                    'value' => '20.40',
                    'type' => 'other',
                    'title' => 'title',
                    'description' => 'description',
                    'valuation_date' => '2025-01-20T00:00:00+00:00',
                ],
            ],
        ];
    }
}

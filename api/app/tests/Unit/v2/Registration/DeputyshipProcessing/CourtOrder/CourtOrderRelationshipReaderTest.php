<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Domain\CourtOrder\CourtOrderKind;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;

class CourtOrderRelationshipReaderTest extends TestCase
{
    public function testRead()
    {
        $connection = $this->createStub(Connection::class);
        $result = $this->createStub(Result::class);
        $reader = new CourtOrderRelationshipReader($connection);

        $connection->method('executeQuery')->willReturn($result);
        $result->method('iterateAssociative')->willReturn(new \ArrayIterator([
            ['order_id' => 1, 'sibling_id' => null, 'kind' => 'single'],
            ['order_id' => 3, 'sibling_id' => 7, 'kind' => 'dual'],
            ['order_id' => 5, 'sibling_id' => 9, 'kind' => 'hybrid'],
        ]));

        /**
         * @var array<array<int, CourtOrderKind, int|null>> $expectations
         */
        $expectations = [
            [1, CourtOrderKind::Single, null],
            [3, CourtOrderKind::Dual, 7],
            [5, CourtOrderKind::Hybrid, 9],
        ];

        foreach ($reader->read() as $relationship) {
            $expectation = array_shift($expectations);
            $this->assertSame($expectation[0], $relationship->courtOrderId);
            $this->assertSame($expectation[1], $relationship->kind);
            $this->assertSame($expectation[2], $relationship->siblingId);
        }
        $this->assertEmpty($expectations);
    }
}

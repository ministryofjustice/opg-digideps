<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\ClientIdFixDataFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ClientIdFixDataFactoryTest extends TestCase
{
    public function testRunDbException(): void
    {
        $conn = self::createMock(Connection::class);
        $conn->expects(self::once())->method('executeQuery')->willThrowException(new Exception('DB error'));

        $em = self::createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getConnection')->willReturn($conn);

        $sut = new ClientIdFixDataFactory($em);

        $dataFactoryResult = $sut->run();

        $this->assertFalse($dataFactoryResult->getSuccess());
    }
}

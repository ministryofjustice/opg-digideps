<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Doctrine\DBAL\Connection;
use ReflectionObject;

class HealthControllerTest extends AbstractTestController
{
    public function testContainerHealthOk()
    {
        $ret = $this->assertJsonRequest('GET', '/health-check', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals('ok', $ret);

        $this->validateResponseAgainstOpenApiSpecification('/health-check', 'get');
    }

    public function testServiceHealthOk()
    {
        $ret = $this->assertJsonRequest('GET', '/health-check/service', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals(1, $ret['healthy'], print_r($ret, true));
        $this->assertEquals('', $ret['errors']);

        $this->validateResponseAgainstOpenApiSpecification('/health-check/service', 'get');
    }

    public function testServiceHealthError()
    {
        $this->breakTheDBConnection();

        $ret = $this->assertJsonRequest('GET', '/health-check/service', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals(false, $ret['healthy'], print_r($ret, true));
        $this->assertEquals('Database generic error', $ret['errors']);

        $this->validateResponseAgainstOpenApiSpecification('/health-check/service', 'get');
    }

    public function breakTheDBConnection(): void
    {
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        $mockConnection = $this->createMock(Connection::class);
        $mockConnection->method('connect')
            ->willThrowException(new \Exception('Database connection failed'));

        $ref = new ReflectionObject($connection);
        $prop = $ref->getProperty('_conn');
        $prop->setValue($connection, $mockConnection);
    }
}

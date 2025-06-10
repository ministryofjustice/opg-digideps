<?php

namespace App\Tests\Integration\Controller;

use Doctrine\DBAL\Connection;
use Osteel\OpenApi\Testing\ValidatorBuilder;
use ReflectionObject;

class HealthControllerTest extends AbstractTestController
{
    public function testServiceHealth()
    {
        $ret = $this->assertJsonRequest('GET', '/health-check/service', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals(1, $ret['healthy'], print_r($ret, true));
        $this->assertEquals('', $ret['errors']);
    }

    public function testContainerHealth()
    {
        $ret = $this->assertJsonRequest('GET', '/health-check', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals('ok', $ret);
    }

    public function testHealthCheckOk()
    {
        self::$frameworkBundleClient->request('GET', '/health-check');

        $validator = ValidatorBuilder::fromYamlFile(__DIR__ . '/temp/api-docs.yaml')->getValidator();

        $this->assertTrue($validator->validate(self::$frameworkBundleClient->getResponse(), '/health-check', 'get'));
    }

    public function testServiceHealthCheckOk()
    {
        self::$frameworkBundleClient->request('GET', '/health-check/service');

        $validator = ValidatorBuilder::fromYamlFile(__DIR__ . '/temp/api-docs.yaml')->getValidator();

        $this->assertTrue($validator->validate(self::$frameworkBundleClient->getResponse(), '/health-check/service', 'get'));
    }

    public function testServiceHealthCheckError()
    {
        $this->breakTheDBConnection();

        self::$frameworkBundleClient->request('GET', '/health-check/service');

        $validator = ValidatorBuilder::fromYamlFile(__DIR__ . '/temp/api-docs.yaml')->getValidator();

        $response = self::$frameworkBundleClient->getResponse();

        $this->assertTrue($validator->validate($response, '/health-check/service', 'get'));
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

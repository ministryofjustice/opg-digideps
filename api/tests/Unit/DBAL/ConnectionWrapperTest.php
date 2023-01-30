<?php

namespace App\Tests\Unit\DBAL;

use App\DBAL\ConnectionWrapper;
use Aws\SecretsManager\Exception\SecretsManagerException;
use Aws\SecretsManager\SecretsManagerClient;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use PHPUnit\Framework\TestCase;
use Predis\Client as PredisClient;

class ConnectionWrapperTest extends TestCase
{
    public function testConnect()
    {
        // Create mocks for the dependencies
        $driver = $this->createMock(Driver::class);
        $config = $this->createMock(Configuration::class);
        $eventManager = $this->createMock(EventManager::class);
        $redis = $this->getMockClass('Predis\Client', [['exists', 'get', 'set']]);
        $secretClient = $this->createMock(SecretsManagerClient::class);

        // Set up the mock for the connect method of the driver
        $conn = $this->createMock(Connection::class);
        $driver->expects($this->once())->method('connect')->will($this->returnValue($conn));

        // Set up the mock for the autoCommit method of the configuration
        $config->expects($this->once())->method('getAutoCommit')->will($this->returnValue(false));

        // Set up the mocks for the get and set methods of the RedisClient
        $redis->expects($this->once())->method('get')->will($this->returnValue(false));

        $redis->expects($this->once())->method('set')->with(
            ConnectionWrapper::DB_PASSWORD,
            'initial_pw'
        );

        // Set up the mock for the refreshPassword method
        $secretClient->expects($this->once())->method('getSecretValue')->willThrowException(
            new SecretsManagerException('Test exception')
        );

        $params = [
            'dbname' => 'testdb',
            'user' => 'testuser',
            'password' => 'testpassword',
            'host' => 'testhost',
            'driver' => 'pdo_postgresql',
        ];

        // Create an instance of the ConnectionWrapper and call the connect method
        $wrapper = new ConnectionWrapper(
            $params,
            $driver,
            $config,
            $eventManager
        );
//        $wrapper->setRedis($redis);
//        $wrapper->setSecretsManagerClient($secretClient);
        $result = $wrapper->connect();

        // Assert the result of the connect method
        $this->assertTrue($result);
    }

    public function testRefreshPassword()
    {
        $params = [
            'dbname' => 'testdb',
            'user' => 'testuser',
            'password' => 'testpassword',
            'host' => 'testhost',
            'driver' => 'pdo_postgresql',
        ];

        $mockDriver = $this->createMock(Driver::class);
        $mockConfig = $this->createMock(Configuration::class);
        $mockEventManager = $this->createMock(EventManager::class);

        $mockConfig->method('getAutoCommit')
            ->willReturn(true);

        $mockRedis = $this->createMock(PredisClient::class);
        $mockRedis->method('get')
            ->willReturn(null);

        $mockRedis->method('set')
            ->willReturn(true);

        $mockSecretClient = $this->createMock(SecretsManagerClient::class);
        $mockSecretClient->method('getSecretValue')
            ->willReturn(['SecretString' => 'newsecret']);

        $connectionWrapper = $this->getMockBuilder(ConnectionWrapper::class)
            ->setConstructorArgs([$params, $mockDriver, $mockConfig, $mockEventManager])
            ->setMethods(['setRedis', 'setSecretsManagerClient'])
            ->getMock();

        $connectionWrapper->expects($this->once())
            ->method('setRedis')
            ->with($this->equalTo($mockRedis));

        $connectionWrapper->expects($this->once())
            ->method('setSecretsManagerClient')
            ->with($this->equalTo($mockSecretClient));

        $connectionWrapper->connect();

        $reflectedPassword = new ReflectionProperty(ConnectionWrapper::class, 'params');
        $reflectedPassword->setAccessible(true);
        $params = $reflectedPassword->getValue($connectionWrapper);

        $this->assertEquals('newsecret', $params['password']);
    }
}

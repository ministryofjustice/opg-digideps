<?php

//
// declare(strict_types=1);
//
// namespace App\Tests\Unit\DBAL;
//
// use PHPUnit\Framework\TestCase;
// use App\DBAL\ConnectionWrapper;
// use Doctrine\DBAL\Driver;
// use Doctrine\DBAL\Configuration;
// use Aws\SecretsManager\SecretsManagerClient;
// use Doctrine\Common\EventManager;
// use Predis\Client as PredisClient;
//
// class ConnectionWrapperTest extends TestCase
// {
//    private $connectionWrapper;
//    private $driverMock;
//    private $configMock;
//    private $eventManagerMock;
//    private $secretsManagerClientMock;
//    private $predisClientMock;
//
//    protected function setUp(): void
//    {
//        $this->driverMock = $this->createMock(Driver::class);
//        $this->configMock = $this->createMock(Configuration::class);
//        $this->eventManagerMock = $this->createMock(EventManager::class);
//        $this->secretsManagerClientMock = $this->createMock(SecretsManagerClient::class);
//        $this->predisClientMock = $this->createMock(PredisClient::class);
//
//        $params = [
//            'user' => 'myuser',
//            'password' => 'mypassword',
//            'host' => 'myhost',
//            'dbname' => 'mydb',
//        ];
//
//        $this->connectionWrapper = new ConnectionWrapper(
//            $params,
//            $this->driverMock,
//            $this->configMock,
//            $this->eventManagerMock
//        );
//        $this->connectionWrapper->setSecretsManagerClient($this->secretsManagerClientMock);
//        $this->connectionWrapper->setPredisClient($this->predisClientMock);
//    }
//
//    public function testConnect()
//    {
//        $this->predisClientMock->expects($this->once())
//            ->method('get')
//            ->with(ConnectionWrapper::DB_PASSWORD)
//            ->willReturn('mypassword');
//        $this->driverMock->expects($this->once())
//            ->method('connect')
//            ->with([
//                'user' => 'myuser',
//                'password' => 'mypassword',
//                'host' => 'myhost',
//                'dbname' => 'mydb',
//            ])
//            ->willReturn('connection');
//
//        $this->configMock->expects($this->once())
//            ->method('getAutoCommit')
//            ->willReturn(true);
//
//        $this->assertFalse($this->connectionWrapper->isConnected());
//        $this->connectionWrapper->connect();
//        $this->assertTrue($this->connectionWrapper->isConnected());
//    }
//
//    public function testConnectFails()
//    {
//        $this->predisClientMock->expects($this->once())
//            ->method('get')
//            ->with(ConnectionWrapper::DB_PASSWORD)
//            ->willReturn(null);
//        $this->driverMock->expects($this->once())
//            ->method('connect')
//            ->with([
//                'user' => 'myuser',
//                'password' => 'mypassword',
//                'host' => 'myhost',
//                'dbname' => 'mydb',
//            ])
//            ->willThrowException(new Driver\Exception());
//        $this->secretsManagerClientMock->expects($this->once())
//            ->method('getSecretValue')
//            ->willReturn(['SecretString' => 'newpassword']);
//        $this->predisClientMock->expects($this->once())
//            ->method('set')
//            ->with(ConnectionWrapper::DB_PASSWORD, 'newpassword');
//        $this->driverMock->expects($this->once())
//            ->method('connect')
//            ->with([
//                'user' => 'myuser',
//                'password' => 'newpassword',
//                'host' => 'myhost',
//                'dbname' => 'mydb',
//            ])
//            ->willReturn('connection');
//
//        $this->configMock->expects($this->once())
//            ->method('getAutoCommit')
//            ->willReturn(true);
//
//        $this->assertFalse($this->connectionWrapper->isConnected());
//        $this->connectionWrapper->connect();
//        $this->assertTrue($this->connectionWrapper->isConnected());
//    }
//
//    public function testRefreshToken()
//    {
//        $this->secretsManagerClientMock->expects($this->once())
//            ->method('getSecretValue')
//            ->with([
//                'SecretId' => 'SECRETS_PREFIX_DBdatabase-password',
//            ])
//            ->willReturn(['SecretString' => 'newpassword']);
//        $this->predisClientMock->expects($this->once())
//            ->method('set')
//            ->with(ConnectionWrapper::DB_PASSWORD, 'newpassword');
//
//        $this->connectionWrapper->refreshToken();
//        $this->assertEquals('newpassword', $this->connectionWrapper->getPassword());
//    }
// }

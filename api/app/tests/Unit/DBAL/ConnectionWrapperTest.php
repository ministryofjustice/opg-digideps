<?php

namespace App\Tests\Unit\DBAL;

use App\DBAL\ConnectionWrapper;
use Aws\SecretsManager\SecretsManagerClient;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

class ConnectionWrapperTest extends TestCase
{
    private ?ConnectionWrapper $connection;

    protected function setUp(): void
    {
        // Database connection settings
        $dbParams = [
            'dbname' => 'api',
            'user' => 'api',
            'password' => 'api',
            'host' => 'postgres',
            'driver' => 'pdo_pgsql',
        ];

        // Create the Doctrine DBAL connection
        $config = new Configuration();
        $pdo = DriverManager::getConnection($dbParams, $config);

        // Create an instance of the ConnectionWrapper
        $this->connection = new ConnectionWrapper($dbParams, $pdo->getDriver(), $config);
    }

    public function testConnect()
    {
        // Connect to a real (not mocked) DB and Secret Manager
        $result = $this->connection->connect();
        $this->assertTrue($result);
        $this->assertTrue($this->connection->isConnected());
    }

    public function testChangePasswordAndConnect()
    {
        // Connect to a real (not mocked) DB, Redis and Secret Manager after changing the DB password
        $secretName = 'local/database-password';
        $oldPassword = 'api';
        $newPassword = 'changedpw';
        $this->updateLocalstackSecret($secretName, $newPassword);

        // Update PostgreSQL master password to changedpw
        $this->updatePostgresMasterPassword($oldPassword, $newPassword);

        // Connect to the database
        $result = $this->connection->connect();
        $this->assertTrue($result);
        $this->assertTrue($this->connection->isConnected());
    }

    private function updateLocalstackSecret(string $secretName, string $newPassword)
    {
        // Use the Secrets Manager client to update the secret value in localstack
        $secretClient = new SecretsManagerClient([
            'region' => 'eu-west-1',
            'version' => '2017-10-17',
            'endpoint' => 'http://localstack:4566',
        ]);

        $secretClient->updateSecret([
            'SecretId' => $secretName,
            'SecretString' => $newPassword,
        ]);
    }

    private function updatePostgresMasterPassword(string $oldPassword, string $newPassword)
    {
        // Update the PostgreSQL master password
        $dbParams = [
            'dbname' => 'api',
            'user' => 'api',
            'password' => $oldPassword,
            'host' => 'postgres',
            'driver' => 'pdo_pgsql',
        ];

        $config = new Configuration();
        $pdo = DriverManager::getConnection($dbParams, $config);

        $pdo->exec("ALTER USER api WITH PASSWORD '{$newPassword}'");
    }

    protected function tearDown(): void
    {
        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
        $this->connection = null;
        try {
            $secretName = 'local/database-password';
            $oldPassword = 'api';
            $newPassword = 'changedpw';
            $this->updateLocalstackSecret($secretName, $oldPassword);
            $this->updatePostgresMasterPassword($newPassword, $oldPassword);
        } catch (\Exception $e) {
            // Do nothing: this is expected to fail if previous test has failed
        }
    }
}

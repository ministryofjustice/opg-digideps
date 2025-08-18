<?php

declare(strict_types=1);

namespace App\DBAL;

use Aws\SecretsManager\Exception\SecretsManagerException;
use Aws\SecretsManager\SecretsManagerClient;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;

class ConnectionWrapper extends Connection
{
    public const SECRETS_PREFIX = 'SECRETS_PREFIX';
    public const SECRETS_ENDPOINT = 'SECRETS_ENDPOINT';

    private bool $_isConnected = false;

    /**
     * @var array|mixed[]
     */
    private array $params;
    private readonly bool $autoCommit;
    private SecretsManagerClient $secretClient;

    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        $secretPrefix = getenv(self::SECRETS_PREFIX);
        $this->setSecretsManagerClient($secretPrefix);
        $this->params = $this->getParams();
        $this->autoCommit = $config->getAutoCommit();
    }

    public function connect()
    {
        if (null !== $this->_conn) {
            return false;
        }

        try {
            $this->_conn = $this->_driver->connect($this->params);
        } catch (Driver\Exception $e) {
            try {
                $this->refreshPassword();
                $this->_conn = $this->_driver->connect($this->params);
            } catch (Driver\Exception $e) {
                throw $this->convertException($e);
            }
        }

        if (false === $this->autoCommit) {
            $this->beginTransaction();
        }

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        $this->_isConnected = true;

        return true;
    }

    protected function refreshPassword()
    {
        $secretPrefix = getenv(self::SECRETS_PREFIX);

        $secretSuffix = 'application' === $this->params['user'] ? 'application-db-password' : 'database-password';

        // Use the Secrets Manager client to retrieve the secret value
        try {
            $result = $this->secretClient->getSecretValue([
                'SecretId' => $secretName,
            ]);
        } catch (SecretsManagerException $e) {
            error_log($e->getMessage());
        }
        // Update local env variable and params with latest password
        // Subsequent connections will use new value stored in redis
        $secretValue = $result['SecretString'];

        $this->params['password'] = $secretValue;
    }

    public function setSecretsManagerClient($secretPrefix)
    {
        if ('local/' == $secretPrefix) {
            $endpoint = getenv(self::SECRETS_ENDPOINT);
            $this->secretClient = new SecretsManagerClient([
                'region' => 'eu-west-1',
                'version' => '2017-10-17',
                'endpoint' => $endpoint,
            ]);
        } else {
            $this->secretClient = new SecretsManagerClient([
                'region' => 'eu-west-1',
                'version' => '2017-10-17',
            ]);
        }
    }

    public function isConnected()
    {
        return $this->_isConnected;
    }

    public function close()
    {
        if ($this->isConnected()) {
            parent::close();
            $this->_isConnected = false;
        }
    }
}

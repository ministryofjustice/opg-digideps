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
use Predis\Client as PredisClient;

class ConnectionWrapper extends Connection
{
    public const DB_PASSWORD = 'DBPassword';
    public const REDIS_DSN = 'REDIS_DSN';
    public const SECRETS_PREFIX = 'SECRETS_PREFIX';
    public const SECRETS_ENDPOINT = 'SECRETS_ENDPOINT';

    private bool $_isConnected = false;

    /**
     * @var array|mixed[]
     */
    private array $params;
    private bool $autoCommit;
    private PredisClient $redis;
    private SecretsManagerClient $secretClient;

    public function __construct(
        array $params, Driver $driver, ?Configuration $config = null, ?EventManager $eventManager = null
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        // Can't be passed in from services.yml as we can't increase number of arguments in unless we wrap driver
        $redis_dsn = getenv(self::REDIS_DSN);
        $secretPrefix = getenv(self::SECRETS_PREFIX);
        $this->setRedis($redis_dsn);
        $this->setSecretsManagerClient($secretPrefix);
        $this->params = $this->getParams();
        $this->autoCommit = $config->getAutoCommit();
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if (null !== $this->_conn) {
            return false;
        }

        $db_password = $this->redis->get(self::DB_PASSWORD);
        // Where password isn't in redis, set one (will be set with real secret when it connects).
        $this->params['password'] = (null == $db_password) ? 'initial_pw' : $db_password;

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
        $secretPrefix = getenv(self::SECRETS_PREFIX_DB);
        $secretName = sprintf('%sdatabase-password', $secretPrefix);

        // Use the Secrets Manager client to retrieve the secret value
        try {
            $result = $this->secretClient->getSecretValue([
                'SecretId' => $secretName,
            ]);
        } catch (SecretsManagerException $e) {
            error_log($e->getMessage());
        }
        // Update redis and params with latest password
        // Subsequent connections will use new value stored in redis
        $secretValue = $result['SecretString'];
        $this->redis->set(self::DB_PASSWORD, $secretValue);
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

    public function setRedis($redis_dsn)
    {
        if (!$redis_dsn) {
            $redis_dsn = 'redis://redis-not-found';
        }
        $this->redis = new PredisClient([
            'scheme' => 'redis',
            'host' => explode('://', $redis_dsn)[1],
            'port' => 6379,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return $this->_isConnected;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if ($this->isConnected()) {
            parent::close();
            $this->_isConnected = false;
        }
    }
}

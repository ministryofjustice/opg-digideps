<?php

declare(strict_types=1);

namespace App\DBAL;

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
    public const REDIS_DSN = 'REDIS_DSN';
    public const DB_PASSWORD = 'DBPassword';
    public const SECRETS_ENDPOINT = 'SECRETS_ENDPOINT';
    public const SECRETS_PREFIX = 'SECRETS_PREFIX_DB';
    //    public const SECRETS_PREFIX = 'SECRETS_PREFIX';

    private bool $_isConnected = false;

    private PredisClient $redis;
    /**
     * @var array|mixed[]
     */
    private array $params;
    private bool $autoCommit;

    public function __construct(
        array $params, Driver $driver, ?Configuration $config = null, ?EventManager $eventManager = null
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        // Can't be passed in from services.yml as we can't increase number of arguments in unless we wrap driver
        $redis_dsn = getenv(self::REDIS_DSN);
        if (!$redis_dsn) {
            $redis_dsn = 'redis://redis-not-found';
        }
        $this->redis = new PredisClient([
            'scheme' => 'redis',
            'host' => explode('://', $redis_dsn)[1],
            'port' => 6379,
        ]);

        $this->params = $this->getParams();

        file_put_contents('php://stderr', print_r('PW_CONSTRUCT: '.substr($this->params['password'], -3), true));
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
        $this->params['password'] = (null == $db_password) ? 'initial_pw' : $db_password;

        file_put_contents('php://stderr', print_r('PW_CONNECT: '.substr($this->params['password'], -3), true));

        try {
            $this->_conn = $this->_driver->connect($this->params);
        } catch (Driver\Exception $e) {
            try {
                $this->refreshToken();
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

    private function refreshToken()
    {
        $endpoint = getenv(self::SECRETS_ENDPOINT);
        $secret_name = sprintf('%sdatabase-password', getenv(self::SECRETS_PREFIX));

        $client = new SecretsManagerClient([
            'region' => 'eu-west-1',
            'version' => '2017-10-17',
            'endpoint' => $endpoint,
        ]);

        file_put_contents('php://stderr', print_r('SECRET_NAMES: '.$secret_name, true));

        // Use the Secrets Manager client to retrieve the secret value
        $result = $client->getSecretValue([
            'SecretId' => $secret_name,
        ]);

        // The secret value is stored in the `SecretString` field of the result
        $secret_value = $result['SecretString'];

        file_put_contents('php://stderr', print_r('SECRET_RESULT: '.substr($secret_value, -3), true));

        $this->redis->set(self::DB_PASSWORD, $secret_value);
        $this->params['password'] = $secret_value;
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

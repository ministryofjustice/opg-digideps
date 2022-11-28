<?php

declare(strict_types=1);

namespace App\DBAL;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Predis\Client as PredisClient;

class ConnectionWrapper extends Connection
{
    public const IAM_AUTH = 'DATABASE_IAM_AUTH';
    public const REDIS_DSN = 'REDIS_DSN';
    public const USER_TOKEN = 'IamUserToken';

    private bool $_isConnected = false;

    private PredisClient $redis;
    /**
     * @var array|mixed[]
     */
    private array $params;
    private string|array|false $iam_auth;
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

        $this->iam_auth = getenv(self::IAM_AUTH);

        if ('1' == $this->iam_auth) {
            $this->params['user'] = 'iamuser';
        }

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

        if ('1' == $this->iam_auth) {
            $token = $this->redis->get(self::USER_TOKEN);
            if (!$token) {
                $this->refreshToken($this->params);
                $token = $this->redis->get(self::USER_TOKEN);
            }

            $this->params['password'] = $token;
        }

        try {
            $this->_conn = $this->_driver->connect($this->params);
        } catch (Driver\Exception $e) {
            throw $this->convertException($e);
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

    private function refreshToken($params)
    {
        $provider = CredentialProvider::defaultProvider();
        $RdsAuthGenerator = new AuthTokenGenerator($provider);
        $token = $RdsAuthGenerator->createToken($params['host'].':'.$params['port'], 'eu-west-1', $params['user']);
        $this->redis->set(self::USER_TOKEN, $token);
        $this->redis->expire(self::USER_TOKEN, 600);
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

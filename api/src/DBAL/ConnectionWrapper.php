<?php

declare(strict_types=1);

namespace App\DBAL;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Exception;
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

    public function __construct(array $params, Driver $driver, ?Configuration $config = null, ?EventManager $eventManager = null)
    {
        parent::__construct($params, $driver, $config, $eventManager);
        $this->redis = new PredisClient([
            'scheme' => 'redis',
            'host' => explode('://', getenv(self::REDIS_DSN))[1],
            'port' => 6379,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }

        $params = $this->getParams();

        $iam_auth = getenv(self::IAM_AUTH);
        file_put_contents('php://stderr', print_r('IAM_AUTH_VALUE: '.$iam_auth, true));

        if ('1' == $iam_auth) {
            file_put_contents('php://stderr', print_r('IAM AUTH ACTIVE', true));
            $params['user'] = 'api';
            if (!$this->redis->get(self::USER_TOKEN)) {
                $this->refreshToken();
            } else {
                file_put_contents('php://stderr', print_r('user_token_exists', true));
            }

            $params['password'] = $this->redis->get(self::USER_TOKEN);
        }

        try {
            $this->_conn = $this->_driver->connect($params);
        } catch (Exception) {
            $this->refreshToken();
            $this->_conn = $this->_driver->connect($params);
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
        file_put_contents('php://stderr', print_r('user_token_not_exists', true));
        //                $provider = CredentialProvider::defaultProvider();
        //                $RdsAuthGenerator = new AuthTokenGenerator($provider);
        //
        //                $token = $RdsAuthGenerator->createToken($params['host'].':'.$params['port'], 'eu-west-1', $params['user']);
        $token = 'api';
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

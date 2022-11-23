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
        $start_time = microtime(true);

        parent::__construct($params, $driver, $config, $eventManager);
        $redis_dsn = getenv(self::REDIS_DSN);
        if (!$redis_dsn) {
            $redis_dsn = 'redis://redis-not-found';
        }
        $this->redis = new PredisClient([
            'scheme' => 'redis',
            'host' => explode('://', $redis_dsn)[1],
            'port' => 6379,
        ]);

        $end_time = microtime(true);
        $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
        file_put_contents('php://stderr', print_r('class constructor setup: '.$execution_time, true));

        $start_time = microtime(true);

        $this->params = $this->getParams();

        $this->iam_auth = getenv(self::IAM_AUTH);

        if ('1' == $this->iam_auth) {
            $this->params['user'] = 'iamuser';
//            $this->params['user'] = 'api';
        }

        $this->autoCommit = $config->getAutoCommit();

        $end_time = microtime(true);
        $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
        file_put_contents('php://stderr', print_r('class constructor other logic: '.$execution_time, true));
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        $start_time = microtime(true);
        if (null !== $this->_conn) {
            $end_time = microtime(true);
            $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
            file_put_contents('php://stderr', print_r('connection false: '.$execution_time, true));

            return false;
        }
        $end_time = microtime(true);
        $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
        file_put_contents('php://stderr', print_r('connection true: '.$execution_time, true));

        $start_time = microtime(true);
        if ('1' == $this->iam_auth) {
            $token = $this->redis->get(self::USER_TOKEN);
            if (!$token) {
                $this->refreshToken($this->params);
                $token = $this->redis->get(self::USER_TOKEN);
            }

            $this->params['password'] = $token;
        }
        $end_time = microtime(true);
        $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
        file_put_contents('php://stderr', print_r('check redis: '.$execution_time, true));

        $start_time = microtime(true);
        try {
            $this->_conn = $this->_driver->connect($this->params);
        } catch (Driver\Exception $e) {
            throw $this->convertException($e);
        }
        $end_time = microtime(true);
        $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
        file_put_contents('php://stderr', print_r('connect to DB: '.$execution_time, true));

        $start_time = microtime(true);
        if (false === $this->autoCommit) {
            $this->beginTransaction();
        }

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        $this->_isConnected = true;

        $end_time = microtime(true);
        $execution_time = substr(strval(($end_time - $start_time) * 1000), 0, 13);
        file_put_contents('php://stderr', print_r('rest of the stuff: '.$execution_time, true));

        return true;
    }

    private function refreshToken($params)
    {
        $provider = CredentialProvider::defaultProvider();
        $RdsAuthGenerator = new AuthTokenGenerator($provider);
//        $token = 'api';
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

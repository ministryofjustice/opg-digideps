<?php

declare(strict_types=1);

namespace App\DBAL;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;

class ConnectionWrapper extends Connection
{
    public const IAM_AUTH = 'DATABASE_IAM_AUTH';

    /**
     * @var bool
     */
    private $_isConnected = false;

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
            $params['user'] = 'iamuser';
            $provider = CredentialProvider::defaultProvider();
            $RdsAuthGenerator = new AuthTokenGenerator($provider);

            $token = $RdsAuthGenerator->createToken($params['host'].':'.$params['port'], 'eu-west-1', $params['user']);
            $params['password'] = $token;
        }
        file_put_contents('php://stderr', print_r('USER SET TO: '.$params['user'], true));
        $this->_conn = $this->_driver->connect($params);

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        $this->_isConnected = true;

        return true;
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

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

final class ConnectionWrapper extends Connection
{
    public const SECRETS_PREFIX = 'SECRETS_PREFIX';
    public const SECRETS_ENDPOINT = 'SECRETS_ENDPOINT';

    private array $params;

    private readonly bool $autoCommit;

    private SecretsManagerClient $secretClient;

    private readonly string $secretPrefix;
    private readonly ?string $secretEndpoint;

    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
    ) {
        parent::__construct($params, $driver, $config, $eventManager);

        // Read env once; allow sensible defaults
        $this->secretPrefix = getenv(self::SECRETS_PREFIX) ?: '';
        $this->secretEndpoint = getenv(self::SECRETS_ENDPOINT) ?: null;

        $this->setSecretsManagerClient();

        $p = $this->getParams();
        $this->params = $p;

        $this->autoCommit = $config ? $config->getAutoCommit() : true;
    }

    /**
     * Establishes the DB connection.
     *
     * @return bool TRUE if the connection was successfully established, FALSE if the connection is already open.
     */
    public function connect(): bool
    {
        if ($this->_conn !== null) {
            return false;
        }

        try {
            $params = $this->params;
            $this->_conn = $this->_driver->connect($params);
        } catch (Driver\Exception) {
            // Attempt to refresh secret and retry once
            $this->refreshPassword();

            try {
                $params = $this->params;
                $this->_conn = $this->_driver->connect($params);
            } catch (Driver\Exception $e) {
                throw $this->convertException($e);
            }
        }

        if ($this->autoCommit === false) {
            $this->beginTransaction();
        }

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        return true;
    }

    /**
     * Retrieves latest DB password from Secrets Manager and updates $this->params.
     */
    protected function refreshPassword(): void
    {
        // Determine secret name based on current user
        $user = (string)($this->params['user'] ?? '');
        $suffix = ($user === 'application') ? 'application-db-password' : 'database-password';
        $secretName = $this->secretPrefix . $suffix;

        try {
            $result = $this->secretClient->getSecretValue(['SecretId' => $secretName]);
        } catch (SecretsManagerException $e) {
            error_log(sprintf('SecretsManager error for "%s": %s', $secretName, $e->getMessage()));
            return;
        }

        $secretValue = $result['SecretString'] ?? null;

        if ($secretValue === null) {
            error_log(sprintf('Secret "%s" has no SecretString', $secretName));
            return;
        }

        // Update the connection params with the new password
        $this->params['password'] = $secretValue;
    }

    /**
     * Configures the Secrets Manager client (LocalStack or AWS).
     */
    private function setSecretsManagerClient(): void
    {
        $base = [
            'region' => 'eu-west-1',
            'version' => '2017-10-17',
        ];

        // Simple local detection: prefix starts with "local/"
        if ($this->secretEndpoint && str_starts_with($this->secretPrefix, 'local/')) {
            $base['endpoint'] = $this->secretEndpoint;
        }

        $this->secretClient = new SecretsManagerClient($base);
    }
}

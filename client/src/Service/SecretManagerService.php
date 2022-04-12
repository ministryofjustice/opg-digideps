<?php

declare(strict_types=1);

namespace App\Service;

use Aws\SecretsManager\SecretsManagerClient;

class SecretManagerService
{
    public const SLACK_APP_TOKEN_SECRET_NAME = 'opg-response-slack-token';

    public function __construct(private SecretsManagerClient $secretsManagerClient, private string $secretPrefix)
    {
    }

    public function getSecret(string $secretName)
    {
        $secretName = sprintf('%s/%s', $this->secretPrefix, $secretName);
        $secret = $this->secretsManagerClient->getSecretValue(['SecretId' => $secretName]);

        return $secret['SecretString'];
    }
}

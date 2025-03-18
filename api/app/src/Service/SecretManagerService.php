<?php

declare(strict_types=1);

namespace App\Service;

use Aws\SecretsManager\SecretsManagerClient;

class SecretManagerService
{
    public const SLACK_APP_TOKEN_SECRET_NAME = 'opg-response-slack-token';
    public const PRIVATE_JWT_KEY_BASE64_SECRET_NAME = 'private-jwt-key-base64';
    public const PUBLIC_JWT_KEY_BASE64_SECRET_NAME = 'public-jwt-key-base64';

    public function __construct(private readonly SecretsManagerClient $secretsManagerClient, private readonly string $secretPrefix)
    {
    }

    public function getSecret(string $secretName)
    {
        $secretName = $this->secretPrefix.$secretName;
        $secret = $this->secretsManagerClient->getSecretValue(['SecretId' => $secretName]);

        return $secret['SecretString'];
    }
}

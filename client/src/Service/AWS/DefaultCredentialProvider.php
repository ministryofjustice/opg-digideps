<?php

declare(strict_types=1);

namespace App\Service\AWS;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;

class DefaultCredentialProvider
{
    /**
     * @return Credentials
     */
    public function getCredentials()
    {
        // Then try loading from default provider.
        $a = CredentialProvider::defaultProvider();
        // Try loading from environment variables. This allows local testing to work with localstack as a fallback.
        $b = CredentialProvider::env();
        // Combine the two providers together.
        $composed = CredentialProvider::chain($a, $b);
        // Returns a promise that is fulfilled with credentials or throws.
        $promise = $composed();
        // Wait on the credentials to resolve.
        return $promise->wait();
    }
}

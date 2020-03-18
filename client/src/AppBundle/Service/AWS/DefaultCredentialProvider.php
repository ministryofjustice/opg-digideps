<?php declare(strict_types=1);


namespace AppBundle\Service\AWS;


use Aws\Credentials\CredentialProvider;

class DefaultCredentialProvider
{
    /**
     * @return callable
     */
    public function getProvider()
    {
        return CredentialProvider::defaultProvider();
    }
}

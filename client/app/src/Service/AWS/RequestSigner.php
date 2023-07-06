<?php

declare(strict_types=1);

namespace App\Service\AWS;

use GuzzleHttp\Psr7\Request;

class RequestSigner
{
    /**
     * @var DefaultCredentialProvider
     */
    private $credentialProvider;

    /**
     * @var SignatureV4Signer
     */
    private $signer;

    public function __construct(DefaultCredentialProvider $credentialProvider, SignatureV4Signer $signer)
    {
        $this->credentialProvider = $credentialProvider;
        $this->signer = $signer;
    }

    public function signRequest(Request $request, string $service)
    {
        $credentials = $this->credentialProvider->getCredentials();

        return $this->signer->signRequest($request, $credentials, $service);
    }
}

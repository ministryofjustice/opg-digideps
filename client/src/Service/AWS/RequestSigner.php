<?php

declare(strict_types=1);

namespace App\Service\AWS;

use GuzzleHttp\Psr7\Request;

class RequestSigner
{
    public function __construct(private DefaultCredentialProvider $credentialProvider, private SignatureV4Signer $signer)
    {
    }

    public function signRequest(Request $request, string $service)
    {
        $credentials = $this->credentialProvider->getCredentials();

        return $this->signer->signRequest($request, $credentials, $service);
    }
}

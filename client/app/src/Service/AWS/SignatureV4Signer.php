<?php

declare(strict_types=1);

namespace App\Service\AWS;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class SignatureV4Signer
{
    public function signRequest(Request $request, Credentials $credentials, string $service, string $region = 'eu-west-1'): RequestInterface|Request
    {
        $signer = new SignatureV4($service, $region);

        return $signer->signRequest($request, $credentials);
    }
}

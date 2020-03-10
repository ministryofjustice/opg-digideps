<?php declare(strict_types=1);

namespace AppBundle\Service\AWS;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;

class RequestSigner
{
    public function signRequest(Request $request, string $service)
    {
        $provider = CredentialProvider::defaultProvider();
        $signer = new SignatureV4($service, 'eu-west-1');
        return $signer->signRequest($request, $provider()->wait());
    }
}

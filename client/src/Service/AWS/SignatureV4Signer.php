<?php declare(strict_types=1);


namespace App\Service\AWS;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;

class SignatureV4Signer
{
    /**
     * @param Request $request
     * @param Credentials $credentials
     * @param string $service
     * @param string $region
     * @return Request|\Psr\Http\Message\RequestInterface
     */
    public function signRequest(Request $request, Credentials $credentials, string $service, string $region='eu-west-1')
    {
        $signer = new SignatureV4($service, $region);
        return $signer->signRequest($request, $credentials);
    }
}

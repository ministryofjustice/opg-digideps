<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\JWT;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JWTService
{
    public function __construct(
        private readonly HttpClientInterface $openInternetClient,
    ) {
    }

    private function decodeAndVerifyWithJWK(JWS $jws, array $jwks): void
    {
        $kid = $jws->getSignature(0)->getProtectedHeader()['kid'];
        $jwk = JWKSet::createFromKeyData($jwks)->get($kid);

        $jwsVerifier = new JWSVerifier(new AlgorithmManager([new RS256()]));

        $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

        if (!$isVerified) {
            throw new \DomainException('Invalid JWS');
        }
    }

    public function getUrn(string $jwt): ?string
    {
        $serializerManager = new JWSSerializerManager([new CompactSerializer()]);
        $jws = $serializerManager->unserialize($jwt);
        $jku = $jws->getSignature(0)->getProtectedHeader()['jku'];

        // Get public key from API
        $jwkResponse = $this->openInternetClient->request('GET', $jku);
        $jwks = json_decode($jwkResponse->getContent(), true);

        $this->decodeAndVerifyWithJWK($jws, $jwks);

        return json_decode($jws->getPayload(), true)['sub'] ?? null;
    }
}

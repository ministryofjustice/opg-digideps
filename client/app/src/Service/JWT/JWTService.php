<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\JWT;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JWTService
{
    private JWSVerifier $jwsVerifier;
    private JWSSerializerManager $serializerManager;

    public function __construct(
        private readonly HttpClientInterface $openInternetClient,
    ) {
        $this->jwsVerifier = new JWSVerifier(new AlgorithmManager([new RS256()]));
        $this->serializerManager = new JWSSerializerManager([new CompactSerializer()]);
    }

    public function getUrn(string $jwt): ?string
    {
        $jws = $this->serializerManager->unserialize($jwt);

        /** @var array<string, string> $protectedHeader */
        $protectedHeader = $jws->getSignature(0)->getProtectedHeader();

        // Get public key from API
        $jwkResponse = $this->openInternetClient->request('GET', $protectedHeader['jku']);

        /** @var array $jwks */
        $jwks = json_decode($jwkResponse->getContent(), true);

        $jwk = JWKSet::createFromKeyData($jwks)->get($protectedHeader['kid']);

        $isVerified = $this->jwsVerifier->verifyWithKey($jws, $jwk, 0);

        if (!$isVerified) {
            throw new \DomainException('Invalid JWS');
        }

        $jwsPayload = $jws->getPayload();

        /** @var array<string, ?string> $payloadArray */
        $payloadArray = is_string($jwsPayload) ? (json_decode($jwsPayload, true) ?? []) : $jwsPayload;

        return $payloadArray['sub'] ?? null;
    }
}

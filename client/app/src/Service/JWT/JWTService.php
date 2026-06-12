<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\JWT;

use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\RSAKey;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JWTService
{
    private Parser $parser;

    public function __construct(
        private readonly HttpClientInterface $openInternetClient,
    ) {
        $this->parser = new Parser(new JoseEncoder());
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTHeaders(string $jwt): array
    {
        $token = $this->parser->parse($jwt);

        return $token->headers()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTClaims(string $jwt): array
    {
        $token = $this->parser->parse($jwt);

        return $token->claims()->all();
    }

    public function getJWTSignature(string $jwt): string
    {
        $token = $this->parser->parse($jwt);

        return $token->signature()->hash();
    }

    public function decodeAndVerifyWithJWK(string $jwt, array $jwks): Token
    {
        $publicKey = $this->getPublicKeyByJWK($jwt, $jwks);

        $token = $this->parser->parse($jwt);

        $validator = new Validator();

        $validator->assert(
            $token,
            new SignedWith(
                new Sha256(),
                InMemory::plainText($publicKey)
            )
        );

        return $token;
    }

    public function getPublicKeyByJWK(string $jwt, array $jwks): string
    {
        $headers = $this->getJWTHeaders($jwt);
        // @TODO rewrite using lcobucci/jwt when https://github.com/lcobucci/jwt/issues/32 is resolved
        $set = JWKSet::createFromKeyData($jwks);

        $jwk = $set->get($headers['kid']); // Same as $json['keys'][0]['kid']

        return RSAKey::createFromJWK($jwk)->toPEM();
    }

    public function getUrn(string $jwt)
    {
        $jwtHeaders = $this->getJWTHeaders($jwt);

        // Get public key from API
        $jwkResponse = $this->openInternetClient->request('GET', $jwtHeaders['jku']);
        $jwks = json_decode($jwkResponse->getContent(), true);

        $decoded = $this->decodeAndVerifyWithJWK($jwt, $jwks);
        return $decoded->claims()->get('sub');
    }
}

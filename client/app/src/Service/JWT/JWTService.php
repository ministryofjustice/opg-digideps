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

    private function decodeAndVerifyWithJWK(Token $token, array $jwks): Token
    {
        $headers = $token->headers()->all();
        $kid = $headers['kid']; // Same as $json['keys'][0]['kid']

        // @TODO rewrite using lcobucci/jwt when https://github.com/lcobucci/jwt/issues/32 is resolved
        $set = JWKSet::createFromKeyData($jwks);
        $jwk = $set->get($kid);

        $publicKey = RSAKey::createFromJWK($jwk)->toPEM();

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

    public function getUrn(string $jwt)
    {
        $token = $this->parser->parse($jwt);

        $jwtHeaders = $token->headers()->all();

        // Get public key from API
        $jwkResponse = $this->openInternetClient->request('GET', $jwtHeaders['jku']);
        $jwks = json_decode($jwkResponse->getContent(), true);

        $decoded = $this->decodeAndVerifyWithJWK($token, $jwks);

        return $decoded->claims()->get('sub');
    }
}

<?php

declare(strict_types=1);

namespace App\Service\JWT;

use Jose\Component\Core\JWKSet;
use Jose\Component\Core\Util\RSAKey;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

class JWTService
{
    public function getJWTHeaders(string $jwt): mixed
    {
        $token = (new Parser(new JoseEncoder()))->parse($jwt);

        return $token->headers()->all();
    }

    public function getJWTClaims(string $jwt): mixed
    {
        $token = (new Parser(new JoseEncoder()))->parse($jwt);

        return $token->claims()->all();
    }

    public function getJWTSignature(string $jwt): string|null
    {
        $token = (new Parser(new JoseEncoder()))->parse($jwt);

        return $token->signature()->hash();
    }

    public function decodeAndVerifyWithJWK(string $jwt, array $jwks): Token
    {
        $publicKey = $this->getPublicKeyByJWK($jwt, $jwks);

        $token = (new Parser(new JoseEncoder()))->parse($jwt);

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

    public static function base64EncodeJWT(array $headers, array $claims, ?array $signature = null)
    {
        $headers = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($headers)));
        $claims = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($claims)));

        $jwt = sprintf('%s.%s', $headers, $claims);

        if (isset($signature)) {
            $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($signature)));
            $jwt = sprintf('%s.%s', $jwt, $signature);
        }

        return $jwt;
    }

    public function getPublicKeyByJWK(string $jwt, array $jwks)
    {
        $headers = $this->getJWTHeaders($jwt);
        $set = JWKSet::createFromKeyData($jwks);

        $jwk = $set->get($headers['kid']); // Same as $json['keys'][0]['kid']

        return RSAKey::createFromJWK($jwk)->toPEM();
    }
}

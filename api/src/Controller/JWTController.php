<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Serializer\SerializerInterface;

class JWTController extends SymfonyAbstractController
{
    private JWTEncoderInterface $JWTEncoder;
    private UserRepository $userRepository;
    private EncoderFactoryInterface $encoderFactory;
    private SerializerInterface $serializer;
    private string $rootDir;

    public function __construct(
        JWTEncoderInterface $JWTEncoder,
        UserRepository $userRepository,
        EncoderFactoryInterface $encoderFactory,
        SerializerInterface $serializer,
        string $rootDir
    ) {
        $this->JWTEncoder = $JWTEncoder;
        $this->userRepository = $userRepository;
        $this->encoderFactory = $encoderFactory;
        $this->serializer = $serializer;
        $this->rootDir = $rootDir;
    }

    /**
     * @Route("/v3/jwt", methods={"POST"})
     */
    public function newToken(Request $request): JsonResponse
    {
        $formData = json_decode($request->getContent(), true)['form-data'];
        $email = $formData['email'];
        $password = $formData['password'];

        /** @var User $user */
        $user = $this->userRepository->findUserByEmail($email);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $passwordEncoder = $this->encoderFactory->getEncoder($user);

        $encodedPass = $passwordEncoder->encodePassword($password, $user->getSalt());
        $isValid = $passwordEncoder->isPasswordValid(
            $encodedPass,
            $password,
            $user->getSalt()
        );

        if (!$isValid) {
            throw new BadCredentialsException();
        }

//        $token = $this->JWTEncoder->encode(['username' => $email]);

        $privateKey = file_get_contents(sprintf('%s/config/jwt/private.pem', $this->rootDir));
        $publicKey = file_get_contents(sprintf('%s/config/jwt/public.pem', $this->rootDir));

        $kid = openssl_digest($publicKey, 'sha256');
        $payload = [
            'username' => $email,
            'userId' => $user->getId(),
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256', $kid);

        return new JsonResponse(
            [
                'token' => $jwt,
                'user' => $this->serializer->serialize($user, 'json'),
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/v3/jwk-public-key", methods={"GET"})
     */
    public function getPublicJwkKey()
    {
        $publicKey = file_get_contents(sprintf('%s/config/jwt/public.pem', $this->rootDir));
        $kid = openssl_digest($publicKey, 'sha256');

        $keyInfo = openssl_pkey_get_details(openssl_pkey_get_public($publicKey));

        $jsonData = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'n' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyInfo['rsa']['n'])), '='),
                    'e' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($keyInfo['rsa']['e'])), '='),
                    'kid' => $kid,
                    'alg' => 'RS256',
                    'use' => 'sig',
                ],
            ],
        ];

        return new JsonResponse($jsonData);
    }
}

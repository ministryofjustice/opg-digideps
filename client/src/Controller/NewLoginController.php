<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use App\Service\Client\Internal\RegistrationApi;
use App\Service\Client\TokenStorage\RedisStorage;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class NewLoginController extends AbstractController
{
    private HttpClientInterface $phpApiClient;
    private RedisStorage $tokenStorage;
    private SerializerInterface $serializer;
    private string $rootDir;
    private RegistrationApi $registrationApi;

    public function __construct(
        HttpClientInterface $phpApiClient,
        RedisStorage $tokenStorage,
        SerializerInterface $serializer,
        string $rootDir,
        RegistrationApi $registrationApi
    ) {
        $this->phpApiClient = $phpApiClient;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->rootDir = $rootDir;
        $this->registrationApi = $registrationApi;
    }

    // Move to regular login controller and break below into service to be called

    /**
     * @Route("/v2/JWTlogin", name="jwt_login")
     * @Template("@App/Index/login.html.twig")
     *
     * @return Response|null
     */
    public function JWTlogin(Request $request)
    {
        $form = $this->createForm(LoginType::class, null, [
            'action' => $this->generateUrl('jwt_login'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Log in to new login endpoint that will call JWT in Golang
            $response = $this->phpApiClient->request('POST', 'jwt', [
                'json' => [
                    'form-data' => [
                        'email' => $data['email'], 'password' => $data['password'],
                    ],
                ],
            ]);

            // Get public key from API
            $jwkResponse = $this->phpApiClient->request('GET', 'jwk-public-key');
            $jwks = json_decode($jwkResponse->getContent(), true);

            // Get JWT response and save into session for user

            $jwt = json_decode($response->getContent(), true)['token'];
            try {
                $decoded = JWT::decode($jwt, JWK::parseKeySet($jwks), ['RS256']);
                // Do we want to wait for validating JWT and THEN get user based on user value in payload rather than returning alongside payload?
//                $userEmail = (array) $decoded['username'];
            } catch (Throwable) {
                throw new RuntimeException('Problems authenticating - try again');
            }

            $userJson = json_decode($response->getContent(), true)['user'];

            $user = $this->serializer->deserialize($userJson, 'App\Entity\User', 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

            $this->tokenStorage->set($user->getId(), $jwt);
            $response = $this->registrationApi->getMyRequestInfo();

            return new Response($response);
        }

        return $this->render('@App/Index/login.html.twig', ['form' => $form->createView(), 'isAdmin' => false]);
    }

    /**
     * @Route("/v2/jwks", methods={"GET"})
     */
    public function publicJwks(Request $request): JsonResponse
    {
        $response = $this->phpApiClient->request('GET', 'jwk-public-key');
        $publicKey = json_decode($response->getContent(), true)['public_key'];

        return new JsonResponse(['public_key' => $publicKey]);
    }
}

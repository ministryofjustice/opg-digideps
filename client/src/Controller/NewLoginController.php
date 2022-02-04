<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\LoginType;
use App\Service\Client\TokenStorage\RedisStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NewLoginController extends AbstractController
{
    private HttpClientInterface $phpApiClient;
    private RedisStorage $tokenStorage;
    private SerializerInterface $serializer;

    public function __construct(HttpClientInterface $phpApiClient, RedisStorage $tokenStorage, SerializerInterface $serializer)
    {
        $this->phpApiClient = $phpApiClient;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/JWTlogin", name="jwt_login")
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
                        'email' => $data['email'], 'password' => $data['password'
                        ],
                    ],
                ],
            ]);

            // Get JWT response and save into session for user

            $token = json_decode($response->getContent(), true)['token'];
            $userJson = json_decode($response->getContent(), true)['user'];

            $user = $this->serializer->deserialize($userJson, 'App\Entity\User', 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

//            $response = $this->apiCall('post', '/auth/login', $credentials, 'response', [], false);
//
//            /** @var User */
//            $user = $this->arrayToEntity('User', $this->extractDataArray($response));
//
//            // store auth token
//            $tokenVal = $response->getHeader(self::HEADER_AUTH_TOKEN);
//            $tokenVal = is_array($tokenVal) && !empty($tokenVal[0]) ? $tokenVal[0] : null;
            $this->tokenStorage->set($user->getId(), $token);

            $test = '';

            return new Response();
        }

        return $this->render('@App/Index/login.html.twig', ['form' => $form->createView(), 'isAdmin' => false]);
    }
}

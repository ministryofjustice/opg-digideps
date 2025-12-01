<?php

declare(strict_types=1);

namespace App\Controller\JWT;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JWTController extends AbstractController
{
    public function __construct(private readonly HttpClientInterface $phpApiClient)
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route(path: '/v2/.well-known/jwks.json', name: 'jwks')]
    public function jwks(): JsonResponse
    {
        $jwkResponse = $this->phpApiClient->request('GET', 'jwk-public-key');

        return new JsonResponse(json_decode($jwkResponse->getContent(), true));
    }
}

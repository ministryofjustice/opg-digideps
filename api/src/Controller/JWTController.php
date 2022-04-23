<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\JWT\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JWTController extends AbstractController
{
    public function __construct(private JWTService $JWTService)
    {
    }

    /**
     * @Route("/v3/jwk-public-key", methods={"GET"})
     */
    public function getPublicJwkKey(): JsonResponse
    {
        return new JsonResponse($this->JWTService->generateJWK());
    }
}

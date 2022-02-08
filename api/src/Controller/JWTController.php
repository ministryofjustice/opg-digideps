<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\JWT\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JWTController extends SymfonyAbstractController
{
    private JWTService $JWTService;

    public function __construct(JWTService $JWTService)
    {
        $this->JWTService = $JWTService;
    }

    /**
     * @Route("/v3/jwk-public-key", methods={"GET"})
     */
    public function getPublicJwkKey()
    {
        return new JsonResponse($this->JWTService->generateJWK());
    }
}

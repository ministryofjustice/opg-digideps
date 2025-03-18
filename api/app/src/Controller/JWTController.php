<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Auth\AuthService;
use App\Service\JWT\JWTService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JWTController extends AbstractController
{
    public function __construct(
        private readonly JWTService $JWTService,
        private readonly LoggerInterface $logger,
        private readonly AuthService $authService
    ) {
    }

    /**
     * @Route("/v3/jwk-public-key", methods={"GET"})
     */
    public function getPublicJwkKey(): JsonResponse
    {
        try {
            $this->logger->warning('Serving JWK');
            $jwk = $this->JWTService->generateJWK();
        } catch (\Throwable $e) {
            $message = sprintf('Error Serving JWK: %s', $e->getMessage());
            $this->logger->warning($message);
            throw $e;
        }

        return new JsonResponse($jwk);
    }
}

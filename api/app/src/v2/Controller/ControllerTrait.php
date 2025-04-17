<?php

namespace App\v2\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ControllerTrait
{
    private function buildSuccessResponse(array $data, string $message = '', int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function buildNotFoundResponse(string $message = ''): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    private function buildErrorResponse(string $message = '', int $status = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}

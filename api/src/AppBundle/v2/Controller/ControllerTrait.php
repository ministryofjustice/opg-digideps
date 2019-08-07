<?php

namespace AppBundle\v2\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ControllerTrait
{
    /**
     * @param array $data
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    private function buildSuccessResponse(array $data, $message = '', $status = 200)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    private function buildNotFoundResponse($message = '')
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message
        ], 404);
    }
}

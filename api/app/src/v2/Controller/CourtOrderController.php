<?php

declare(strict_types=1);

namespace App\v2\Controller;

use App\Controller\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/courtorder')]
class CourtOrderController extends RestController
{
    use ControllerTrait;

    /**
     * path on API = /v2/courtorder/<UID>.
     */
    #[Route('/{uid}', requirements: ['uid' => '\w+'], methods: ['GET'])]
    public function getByUidAction(string $uid): JsonResponse
    {
        return $this->buildSuccessResponse(['ok' => true]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JWTController extends RestController
{
    /**
     * @Route("/api/tokens", methods={"POST"})
     */
    public function newTokenAction()
    {
        return new Response('TOKEN!');
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends Controller
{
    /**
     * @param int $statusCode
     *
     * @return Response
     */
    protected function renderError(string $description, $statusCode = 500)
    {
        $text = $this->renderView('bundles/TwigBundle/Exception/template.html.twig', [
            'message' => 'Application error',
            'description' => $description,
        ]);

        return new Response($text, $statusCode);
    }
}

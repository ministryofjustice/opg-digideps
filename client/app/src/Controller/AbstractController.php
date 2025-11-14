<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends SymfonyAbstractController
{
    protected function renderError(string $description, int $statusCode = 500, $message = 'Application error'): Response
    {
        $text = $this->renderView('bundles/TwigBundle/Exception/template.html.twig', [
            'message' => $message,
            'description' => $description,
        ]);

        return new Response($text, $statusCode);
    }
}

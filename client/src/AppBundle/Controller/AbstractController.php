<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends Controller
{
    /**
     * @param string $description
     * @param int $statusCode
     * @return Response
     */
    protected function renderError(string $description, $statusCode = 500)
    {
        $text = $this->renderView('TwigBundle:Exception:template.html.twig', [
            'message' => 'Application error',
            'description' => $description
        ]);

        return new Response($text, $statusCode);
    }
}

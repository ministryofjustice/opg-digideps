<?php

namespace AppBundle\Controller;

use AppBundle\Service\StepRedirector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

abstract class AbstractController extends Controller
{
    /**
     * @return object
     */
    protected function stepRedirector()
    {
        /** @var StepRedirector */
        return $this->get(StepRedirector::class);
    }

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

    /**
     * @return object
     */
    public function getRouter()
    {
        /** @var Router */
        return $this->get('router');
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    protected function routeExists(string $route)
    {
        return $this->getRouter()->getRouteCollection()->get($route) ? true : false;
    }
}

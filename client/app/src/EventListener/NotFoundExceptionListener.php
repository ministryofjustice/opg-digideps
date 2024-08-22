<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class NotFoundExceptionListener
{
    private $logger;

    private $twig;

    public function __construct(LoggerInterface $logger, Environment $environment)
    {
        $this->logger = $logger;
        $this->twig = $environment;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            // Log as a warning instead of an error
            $this->logger->warning('Not found exception: '.$exception->getMessage());
            $html = $this->twig->render('bundles/TwigBundle/Exception/error404.html.twig', []);
            $response = new Response($html, 404);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}

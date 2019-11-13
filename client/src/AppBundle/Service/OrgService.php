<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\TokenStorage\RedisStorage;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class OrgService
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var RedisStorage
     */
    private $tokenStorage;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var bool
     */
    private $outputToStream = false;

    /**
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient, Client $client, RedisStorage $tokenStorage, Environment $twig, Router $router, Session $session)
    {
        $this->restClient = $restClient;
        $this->client = $client;
        $this->tokenStorage = $tokenStorage;
        $this->twig = $twig;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @param bool $outputToStream
     */
    public function setOutputStream(bool $outputToStream)
    {
        $this->outputToStream = $outputToStream;
    }

    protected function generateResponse($request)
    {
        $response = new StreamedResponse();
        $stream = $request->getBody();

        $response->setCallback(function() use ($stream) {
            $flashBag = $this->session->getFlashBag();

            $errors = [];
            $warnings = [];
            $added = [
                'prof_users' => 0,
                'pa_users' => 0,
                'clients' => 0,
                'reports' => 0,
            ];

            $carryover = '';

            while (!$stream->eof()) {
                $partial = $carryover . $stream->read(1024);
                $carryover = '';

                $lines = explode("\n", $partial);

                // If partial didn't finish with a newline, carry over the last line
                if ($lines[count($lines) - 1] !== '') {
                    $carryover = array_pop($lines);
                }

                foreach ($lines as $line) {
                    if (substr($line, 0, 5) === 'PROG ' && $this->outputToStream) {
                        echo $line . "\n";
                        flush();
                    } else if (substr($line, 0, 4) === 'ERR ') {
                        $errors[] = substr($line, 4);
                    } else if (substr($line, 0, 5) === 'WARN ') {
                        $warnings[] = substr($line, 5);
                    } else if (substr($line, 0, 4) === 'ADD ') {
                        list(, $amount, $key) = explode(' ', $line);
                        $added[strtolower($key)] += $amount;
                    }
                }

                gc_collect_cycles();
            }

            if (count($errors)) {
                $flash = $this->twig->render('AppBundle:Admin/Index:_uploadErrorAlert.html.twig', [
                    'type' => 'errors',
                    'errors' => $errors,
                ]);

                $flashBag->add('error', $flash);
            }

            if (count($warnings)) {
                $flash = $this->twig->render('AppBundle:Admin/Index:_uploadErrorAlert.html.twig', [
                    'type' => 'warnings',
                    'errors' => $warnings,
                ]);

                $flashBag->add('warning', $flash);
            }

            $flashBag->add(
                'notice',
                sprintf('Added %d Prof users, %d PA users, %d clients and %d reports. Go to users tab to enable them',
                    $added['prof_users'],
                    $added['pa_users'],
                    $added['clients'],
                    $added['reports']
                )
            );

            $redirectUrl = $this->router->generate('admin_org_upload');

            if ($this->outputToStream) {
                echo "REDIR $redirectUrl\n";
                echo "END\n";
                flush();
            } else {
                header('Location: '. $redirectUrl);
            }

            die();
        });

        $response->setStatusCode(200);
        if ($this->outputToStream) {
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        }

        return $response;
    }

    /**
     * @param mixed $compressedData
     */
    public function upload($compressedData, User $currentUser)
    {
        $request = $this->client->post('org/bulk-add', [
            'headers' => [
                'AuthToken' => $this->tokenStorage->get($currentUser->getId())
            ],
            'body' => json_encode($compressedData),
            'timeout' => 600,
            'stream' => true,
        ]);

        return $this->generateResponse($request);
    }
}

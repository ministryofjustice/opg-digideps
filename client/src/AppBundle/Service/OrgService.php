<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
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
     * @var Environment
     */
    private $twig;

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
    public function __construct(RestClient $restClient, Environment $twig, Session $session)
    {
        $this->restClient = $restClient;
        $this->twig = $twig;
        $this->session = $session;
    }

    /**
     * @param bool $outputToStream
     */
    public function setOutputStream(bool $outputToStream)
    {
        $this->outputToStream = $outputToStream;
    }

    protected function generateResponse($stream, $redirectUrl)
    {
        $response = new StreamedResponse();

        $response->setCallback(function() use ($stream, $redirectUrl) {
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
    public function upload($compressedData, $redirectUrl)
    {
        $stream = $this->restClient->apiCall(
            'post',
            'org/bulk-add',
            json_encode($compressedData),
            'raw',
            [
                'timeout' => 600,
                'stream' => true,
            ]
        );

        return $this->generateResponse($stream, $redirectUrl);
    }
}

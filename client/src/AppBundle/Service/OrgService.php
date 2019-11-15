<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
use GuzzleHttp\Psr7\Stream;
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
    private $outputLogging = false;

    /**
     * @var array
     */
    private $output = [
        'errors' => [],
        'warnings' => [],
        'added' => [
            'prof_users' => 0,
            'pa_users' => 0,
            'clients' => 0,
            'reports' => 0,
        ],
    ];

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
     * @param bool $outputLogging
     * @return $this
     */
    public function setLogging(bool $outputLogging)
    {
        $this->outputLogging = $outputLogging;
        return $this;
    }

    /**
     * Push some output to the buffer, if enabled
     *
     * @param string $output
     */
    protected function log(string $output)
    {
        if (!$this->outputLogging) return;

        echo $output . "\n";
        flush();
    }

    /**
     * Parse a line of the API output
     *
     * @param string $line
     */
    protected function parseLine(string $line)
    {
        if (substr($line, 0, 5) === 'PROG ') {
            $this->log($line);
        } else if (substr($line, 0, 4) === 'ERR ') {
            $this->output['errors'][] = substr($line, 4);
        } else if (substr($line, 0, 5) === 'WARN ') {
            $this->output['warnings'][] = substr($line, 5);
        } else if (substr($line, 0, 4) === 'ADD ') {
            list(, $amount, $key) = explode(' ', $line);
            $this->output['added'][strtolower($key)] += $amount;
        }
    }

    /**
     * Set flash messages about results of upload
     */
    protected function addFlashMessages()
    {
        $flashBag = $this->session->getFlashBag();

        if (count($this->output['errors'])) {
            $flash = $this->twig->render('AppBundle:Admin/Index:_uploadErrorAlert.html.twig', [
                'type' => 'errors',
                'errors' => $this->output['errors'],
            ]);

            $flashBag->add('error', $flash);
        }

        if (count($this->output['warnings'])) {
            $flash = $this->twig->render('AppBundle:Admin/Index:_uploadErrorAlert.html.twig', [
                'type' => 'warnings',
                'errors' => $this->output['warnings'],
            ]);

            $flashBag->add('warning', $flash);
        }

        $flashBag->add(
            'notice',
            sprintf('Added %d Prof users, %d PA users, %d clients and %d reports. Go to users tab to enable them',
                $this->output['added']['prof_users'],
                $this->output['added']['pa_users'],
                $this->output['added']['clients'],
                $this->output['added']['reports']
            )
        );
    }

    /**
     * Generate a streamed response
     *
     * @return StreamedResponse
     */
    protected function generateStreamedResponse()
    {
        $response = new StreamedResponse();
        $response->setStatusCode(200);

        if ($this->outputLogging) {
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        }

        return $response;
    }

    /**
     * When the API stream has finished, force a redirect
     *
     * @param string $redirectUrl
     */
    protected function finishStream($redirectUrl)
    {
        $this->addFlashMessages();
        $this->log("REDIR $redirectUrl");
        $this->log('END');

        if (!$this->outputLogging) {
            header('Location: '. $redirectUrl);
            echo ' ';
        }
    }

    /**
     * Convert the API stream into suitable output
     *
     * @param Stream $stream
     * @param string $redirectUrl
     */
    protected function convertStreamToOutput(Stream $stream, string $redirectUrl)
    {
        if ($this->outputLogging) {
            $this->session->start();
        }

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
                $this->parseLine($line);
            }

            gc_collect_cycles();
        }

        $this->finishStream($redirectUrl);

        die();
    }

    /**
     * @param mixed $data
     * @return Stream
     */
    public function upload($data)
    {
        /** @var Stream $stream */
        $stream = $this->restClient->apiCall(
            'post',
            'org/bulk-add',
            json_encode($data),
            'raw',
            [
                'timeout' => 600,
                'stream' => true,
            ]
        );

        return $stream;
    }

    /**
     * @param mixed $data
     * @param string $redirectUrl
     * @return StreamedResponse
     */
    public function process($data, $redirectUrl)
    {
        $stream = $this->upload($data);

        $response = $this->generateStreamedResponse();

        $response->setCallback(function() use ($stream, $redirectUrl) {
            $this->convertStreamToOutput($stream, $redirectUrl);
        });

        return $response;
    }
}

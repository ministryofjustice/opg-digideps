<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\CsvUploader;
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
     * Generate a streamed response
     *
     * @return StreamedResponse
     */
    protected function generateStreamedResponse()
    {
        $response = new StreamedResponse();
        $response->setStatusCode(200);

        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');

        return $response;
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
     * @param int $index
     * @param int $total
     */
    protected function logProgress($index, $total)
    {
        $this->log("PROG $index $total");
    }

    /**
     * Force a redirect and terminate the stream
     *
     * @param string $redirectUrl
     */
    protected function finishStream($redirectUrl)
    {
        $this->log("REDIR $redirectUrl");
        $this->log('END');

        if (!$this->outputLogging) {
            header('Location: '. $redirectUrl);
            echo ' ';
        }
    }

    /**
     * Add the output of a chunk to service collectorss
     *
     * @param array $output
     */
    protected function storeChunkOutput(array $output)
    {
        if (!empty($output['errors'])) {
            $this->output['errors'] = array_merge($this->output['errors'], $output['errors']);
        }

        if (!empty($output['added'])) {
            foreach ($output['added'] as $group => $items) {
                $this->output['added'][$group] += count($items);
            }
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
     * @param array $chunks
     */
    protected function processChunks($chunks)
    {
        $chunkCount = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $compressedChunk = CsvUploader::compressData($chunk);

            /** @var array $upload */
            $upload = $this->restClient->post('org/bulk-add', $compressedChunk);

            $this->storeChunkOutput($upload);
            $this->logProgress($index, $chunkCount);
        }

    }

    /**
     * @param mixed $data
     * @param string $redirectUrl
     * @return StreamedResponse
     */
    public function process($data, $redirectUrl)
    {
        $chunks = array_chunk($data, 100);

        $response = $this->generateStreamedResponse();

        $response->setCallback(function() use ($chunks, $redirectUrl) {
            $this->session->start();

            $this->processChunks($chunks);

            $this->addFlashMessages();
            $this->finishStream($redirectUrl);
        });

        return $response;
    }
}

<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\CsvUploader;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var SessionInterface
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
            'clients' => 0,
            'named_deputies' => 0,
            'reports' => 0
        ],
    ];

    const CHUNK_SIZE = 50;

    /**
     * @param RestClient $restClient
     */
    public function __construct(RestClient $restClient, Environment $twig, SessionInterface $session)
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

        if ($this->outputLogging) {
            $contentType = 'text/plain';
        } else {
            $contentType = 'text/html';
        }

        $response->headers->set('Content-Type', "$contentType; charset=utf-8");
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Push some output to the buffer, if enabled
     *
     * @param string $output
     */
    protected function log(string $output)
    {
        if ($this->outputLogging) {
            echo $output . "\n";
        } else {
            echo ' ';
        }

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
            echo "<meta http-equiv=\"refresh\" content=\"0;url=$redirectUrl\" />";
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

            sprintf('Added %d clients, %d named deputies and %d reports. Go to users tab to enable them',
                $this->output['added']['clients'],
                $this->output['added']['named_deputies'],
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
            $this->logProgress($index + 1, $chunkCount);
        }

    }

    /**
     * @param mixed $data
     * @param string $redirectUrl
     * @return StreamedResponse
     */
    public function process($data, $redirectUrl)
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);

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

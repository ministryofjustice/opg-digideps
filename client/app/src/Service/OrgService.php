<?php

namespace App\Service;

use App\Event\CSVUploadedEvent;
use App\Event\DeputyChangedOrgEvent;
use App\Event\OrgCreatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Audit\AuditEvents;
use App\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class OrgService
{
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
            'deputies' => 0,
            'reports' => 0,
            'organisations' => 0,
        ],
        'updated' => [
            'clients' => 0,
            'deputies' => 0,
            'reports' => 0,
            'organisations' => 0,
        ],
        'skipped' => 0,
    ];

    public const CHUNK_SIZE = 50;

    public function __construct(
        private RestClient $restClient,
        private Environment $twig,
        private SessionInterface $session,
        private ObservableEventDispatcher $eventDispatcher,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * @return $this
     */
    public function setLogging(bool $outputLogging)
    {
        $this->outputLogging = $outputLogging;

        return $this;
    }

    /**
     * Generate a streamed response.
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
     * Push some output to the buffer, if enabled.
     */
    protected function log(string $output)
    {
        if ($this->outputLogging) {
            echo $output."\n";
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
     * Force a redirect and terminate the stream.
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
     * Add the output of a chunk to service collectorss.
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

        if (!empty($output['updated'])) {
            foreach ($output['updated'] as $group => $items) {
                $this->output['updated'][$group] += count($items);
            }
        }

        if (!empty($output['skipped'])) {
            $this->output['skipped'] += $output['skipped'];
        }
    }

    /**
     * Set flash messages about results of upload.
     */
    protected function addFlashMessages()
    {
        $flashBag = $this->session->getFlashBag();

        if (count($this->output['errors'])) {
            $flash = $this->twig->render('@App/Admin/Index/_uploadErrorAlert.html.twig', [
                'type' => 'errors',
                'errors' => $this->output['errors'],
            ]);

            $flashBag->add('error', $flash);
        }

        $flashAddedMessage = sprintf(
            'Added %d clients, %d deputies, %d reports and %d organisations. Skipped %s archived clients. Go to users tab to enable them.',
            $this->output['added']['clients'],
            $this->output['added']['deputies'],
            $this->output['added']['reports'],
            $this->output['added']['organisations'],
            $this->output['skipped'],
        );

        $flashBag->add(
            'notice',
            $flashAddedMessage
        );

        $flashUpdatedMessage = sprintf(
            'Updated details for %d clients, %d deputies, %d reports and %d organisations.',
            $this->output['updated']['clients'],
            $this->output['updated']['deputies'],
            $this->output['updated']['reports'],
            $this->output['updated']['organisations']
        );

        $flashBag->add(
            'notice',
            $flashUpdatedMessage
        );
    }

    /**
     * @param array $chunks
     */
    protected function processChunks($chunks)
    {
        $chunkCount = count($chunks);

        $logged = false;

        foreach ($chunks as $index => $chunk) {
            $compressedChunk = CsvUploader::compressData($chunk);

            /** @var array $upload */
            $upload = $this->restClient->post('v2/org-deputyships', $compressedChunk);

            $this->storeChunkOutput($upload);
            $this->logProgress($index + 1, $chunkCount);

            foreach ($upload['added']['organisations'] as $organisation) {
                $this->dispatchOrgCreatedEvent($organisation);
            }

            foreach ($upload['changeOrg'] as $orgChange) {
                $this->dispatchDeputyChangingOrganisationEvent($orgChange['deputyId'], $orgChange['previousOrgId'], $orgChange['newOrgId'], $orgChange['clientId']);
            }

            if (!$logged) {
                $this->dispatchCSVUploadEvent();
                $logged = true;
            }
        }
    }

    public function process(mixed $data, string $redirectUrl): StreamedResponse
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);

        $response = $this->generateStreamedResponse();

        $response->setCallback(function () use ($chunks, $redirectUrl) {
            $this->session->start();

            $this->processChunks($chunks);

            $this->addFlashMessages();
            $this->finishStream($redirectUrl);
        });

        return $response;
    }

    private function dispatchCSVUploadEvent()
    {
        $csvUploadedEvent = new CSVUploadedEvent(
            'ORG',
            AuditEvents::EVENT_CSV_UPLOADED
        );

        $this->eventDispatcher->dispatch($csvUploadedEvent, CSVUploadedEvent::NAME);
    }

    private function dispatchOrgCreatedEvent(array $organisation)
    {
        $trigger = AuditEvents::TRIGGER_CSV_UPLOAD;
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $orgCreatedEvent = new OrgCreatedEvent(
            $trigger,
            $currentUser,
            $organisation
        );

        $this->eventDispatcher->dispatch($orgCreatedEvent, OrgCreatedEvent::NAME);
    }

    private function dispatchDeputyChangingOrganisationEvent(int $deputyId, int $previousOrgId, int $newOrgId, int $clientIds)
    {
        $trigger = AuditEvents::TRIGGER_DEPUTY_CHANGED_ORG;

        $deputyChangedOrganisationEvent = new DeputyChangedOrgEvent(
            $trigger,
            $deputyId,
            $previousOrgId,
            $newOrgId,
            $clientIds
        );

        $this->eventDispatcher->dispatch($deputyChangedOrganisationEvent, DeputyChangedOrgEvent::NAME);
    }
}

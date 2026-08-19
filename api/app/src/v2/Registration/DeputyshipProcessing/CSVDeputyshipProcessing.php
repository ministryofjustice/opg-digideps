<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing;

use OPG\Digideps\Backend\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use OPG\Digideps\Backend\v2\Registration\Uploader\LayDeputyshipUploader;
use Psr\Log\LoggerInterface;

readonly class CSVDeputyshipProcessing
{
    public function __construct(
        private LayDeputyshipDtoCollectionAssemblerFactory $layFactory,
        private LayDeputyshipUploader $layUploader,
        private LoggerInterface $verboseLogger,
    ) {
    }

    public function layProcessing(array $data, ?int $chunkId): array
    {
        $assembler = $this->layFactory->create();
        $uploadCollection = $assembler->assembleFromArray($data);

        $this->verboseLogger->notice(
            sprintf(
                'Assembled DTO collection from chunkId: %s',
                $chunkId
            )
        );
        $this->verboseLogger->notice(
            sprintf(
                'Size of DTO Collection: %d',
                count($uploadCollection['collection'])
            )
        );
        $result = $this->layUploader->upload($uploadCollection['collection']);
        $result['skipped'] = $uploadCollection['skipped'];

        if (count($result['skipped']) >= 1) {
            foreach ($result['skipped'] as $lineSkipped) {
                $this->verboseLogger->notice(sprintf('Line skipped in CSV due to missing values: %s', $lineSkipped));
            }
        }

        $this->verboseLogger->notice(
            sprintf(
                'Persisted DTO Collection with chunkId: %s',
                $chunkId
            )
        );

        return $result;
    }

    public function layProcessingHandleNewMultiClients(bool $multiclientApplyDbChanges = true): array
    {
        return $this->layUploader->handleNewMultiClients($multiclientApplyDbChanges);
    }

    public function orgProcessing(array $data): array
    {
        $rowCount = count($data);
        $this->verboseLogger->notice("Skipping org ingest. Received {$rowCount} rows.");
        return [];
    }
}

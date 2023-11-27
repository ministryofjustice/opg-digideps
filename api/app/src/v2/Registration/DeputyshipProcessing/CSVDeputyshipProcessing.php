<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;


use App\Service\Formatter\RestFormatter;
use App\v2\Registration\Assembler\SiriusToOrgDeputyshipDtoAssembler;
use App\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use App\v2\Registration\Uploader\LayDeputyshipUploader;
use App\v2\Registration\Uploader\OrgDeputyshipUploader;
use Psr\Log\LoggerInterface;

class CSVDeputyshipProcessing
{
    protected const MAX_UPLOAD_BATCH_SIZE = 10000;
    public function __construct(
        private LayDeputyshipDtoCollectionAssemblerFactory $layFactory,
        private LayDeputyshipUploader $layUploader,
        private OrgDeputyshipUploader $orgUploader,
        private SiriusToOrgDeputyshipDtoAssembler $orgAssembler,
        private RestFormatter $restFormatter,
        private LoggerInterface $logger
    ) {
        
    }
    
    public function layProcessing(array $data, ?int $chunkId)
    {
        $assembler = $this->layFactory->create();
        $uploadCollection = $assembler->assembleFromArray($data);

        $this->logger->info(
            sprintf(
                'Assembled DTO collection from chunkId: %s',
                $chunkId
            )
        );
        $this->logger->info(
            sprintf(
                'Size of DTO Collection: %d',
                count($uploadCollection['collection'])
            )
        );

        $result = $this->layUploader->upload($uploadCollection['collection']);
        $result['skipped'] = $uploadCollection['skipped'];

        $this->logger->info(
            sprintf(
                'Persisted DTO Collection with chunkId: %s',
                $chunkId
            )
        );

        return $result;
    }
    
    public function orgProcessing(array $data)
    {
        $rowCount = count($data);

        $this->restFormatter->setJmsSerialiserGroups(['org-created-event']);

        if (!$rowCount) {
            throw new \RuntimeException('No records received from the API');
        }
        if ($rowCount > self::MAX_UPLOAD_BATCH_SIZE) {
            throw new \RuntimeException(
                sprintf(
                    'Max %s records allowed in a single bulk insert', 
                    self::MAX_UPLOAD_BATCH_SIZE
                )
            );
        }

        $dtos = $this->orgAssembler->assembleMultipleDtosFromArray($data);

        return $this->orgUploader->upload($dtos);
    }
}

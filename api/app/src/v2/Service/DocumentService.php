<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\Report\Document;
use App\Repository\DocumentRepository;
use App\Service\File\Storage\S3Storage;
use Psr\Log\LoggerInterface;

class DocumentService
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly S3Storage $s3Storage,
        private LoggerInterface $logger,
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Delete documents older than the specified number of minutes which have no associated submission.
     * Tag any associated S3 objects with Purge=1, which marks them for automatic deletion by policy.
     */
    public function deleteDocumentsOlderThan(\DateTime $earliestCreatedOn): int
    {
        // find documents older than earliest tolerated created_on datetime with S3 ref
        /** @var Document[] $documents */
        $documents = $this->documentRepository->createQueryBuilder('d')
            ->where('d.createdOn < :earliestCreatedOn')
            ->andWhere('d.storageReference IS NOT NULL')
            ->setParameter('earliestCreatedOn', $earliestCreatedOn)
            ->getQuery()
            ->execute();

        $numDeleted = 0;

        foreach ($documents as $document) {
            $ref = $document->getStorageReference();

            // tag the S3 object for deletion with Purge=1
            $success = $this->s3Storage->tagForDeletion($ref);

            if (!$success) {
                $this->logger->error("Unable to tag S3 object $ref for deletion");
                continue;
            }

            // delete the Document entity
            try {
                $this->documentRepository->delete($document);
                ++$numDeleted;
            } catch (\Exception $e) {
                $this->logger->error(
                    "Could not delete Document entity with ID {$document->getId()} and S3 ref ".
                        "$ref; message = {$e->getMessage()}"
                );
            }
        }

        return $numDeleted;
    }
}

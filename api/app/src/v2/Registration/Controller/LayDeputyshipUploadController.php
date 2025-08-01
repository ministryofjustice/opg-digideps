<?php

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/lay-deputyship')]
class LayDeputyshipUploadController
{
    public function __construct(
        private readonly DataCompression $dataCompression,
        private readonly CSVDeputyshipProcessing $csvProcessing,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route(path: '/upload', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function upload(Request $request): array
    {
        ini_set('memory_limit', '1024M');

        $this->logger->notice(
            sprintf('Uploading chunk with Id: %s',
            $request->headers->get('chunkId')
            )
        );

        $postedData = $this->dataCompression->decompress($request->getContent());

        return $this->csvProcessing->LayProcessing($postedData, $request->headers->get('chunkId'));
    }
}

<?php

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/lay-deputyship')]
class LayDeputyshipUploadController
{
    public function __construct(
        private readonly DataCompression $dataCompression,
        private readonly CSVDeputyshipProcessing $csvProcessing,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     *
     *
     * @return array
     */
    #[Route(path: '/upload', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function upload(Request $request)
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

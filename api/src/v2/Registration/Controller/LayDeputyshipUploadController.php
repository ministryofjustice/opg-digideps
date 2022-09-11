<?php

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use App\v2\Registration\Uploader\LayDeputyshipUploader;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lay-deputyship")
 */
class LayDeputyshipUploadController
{
    public function __construct(
        private DataCompression $dataCompression,
        private LayDeputyshipDtoCollectionAssemblerFactory $factory,
        private LayDeputyshipUploader $uploader,
        private LoggerInterface $verboseLogger
    ) {
    }

    /**
     * @Route("/upload", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return array
     */
    public function upload(Request $request)
    {
        ini_set('memory_limit', '1024M');

        $message = sprintf('Uploading chunk with Id: %s', $request->headers->get('chunkId'));
        $this->verboseLogger->notice($message);

        $postedData = $this->dataCompression->decompress($request->getContent());
        $assembler = $this->factory->create();
        $uploadCollection = $assembler->assembleFromArray($postedData);

        $this->verboseLogger->notice(sprintf('Assembled DTO collection from chunkId: %s', $request->headers->get('chunkId')));
        $this->verboseLogger->notice(sprintf('Size of DTO Collection: %d', count($uploadCollection['collection'])));

        $result = $this->uploader->upload($uploadCollection['collection']);

        $result['skipped'] = $uploadCollection['skipped'];

        $this->verboseLogger->notice(sprintf('Persisted DTO Collection with chunkId: %s', $request->headers->get('chunkId')));

        return $result;
    }
}

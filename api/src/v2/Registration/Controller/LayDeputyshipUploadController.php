<?php

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\v2\Registration\SelfRegistration\Factory\LayDeputyshipDtoCollectionAssemblerFactory;
use App\v2\Registration\Uploader\LayDeputyshipUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lay-deputyship")
 */
class LayDeputyshipUploadController
{
    /** @var DataCompression */
    private $dataCompression;

    /** @var LayDeputyshipDtoCollectionAssemblerFactory */
    private $factory;

    /** @var LayDeputyshipUploader */
    private $uploader;

    public function __construct(
        DataCompression $dataCompression,
        LayDeputyshipDtoCollectionAssemblerFactory $factory,
        LayDeputyshipUploader $uploader
    ) {
        $this->dataCompression = $dataCompression;
        $this->factory = $factory;
        $this->uploader = $uploader;
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

        $postedData = $this->dataCompression->decompress($request->getContent());
        $assembler = $this->factory->create();
        $uploadCollection = $assembler->assembleFromArray($postedData);

        $result = $this->uploader->upload($uploadCollection['collection']);
        $result['skipped'] = $uploadCollection['skipped'];

        return $result;
    }
}

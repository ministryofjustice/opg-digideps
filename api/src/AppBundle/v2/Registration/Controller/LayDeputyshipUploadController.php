<?php

namespace AppBundle\v2\Registration\Controller;

use AppBundle\v2\Registration\Assembler\LayDeputyshipDtoCollectionAssembler;
use AppBundle\Service\DataCompression;
use AppBundle\v2\Registration\Uploader\LayDeputyshipUploader;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/lay-deputyship")
 */
class LayDeputyshipUploadController
{
    /** @var DataCompression */
    private $dataCompression;

    /** @var LayDeputyshipDtoCollectionAssembler */
    private $assembler;

    /** @var LayDeputyshipUploader */
    private $uploader;

    /**
     * @param DataCompression $dataCompression
     * @param LayDeputyshipDtoCollectionAssembler $assembler
     * @param LayDeputyshipUploader $uploader
     */
    public function __construct(
        DataCompression $dataCompression,
        LayDeputyshipDtoCollectionAssembler $assembler,
        LayDeputyshipUploader $uploader
    ) {
        $this->dataCompression = $dataCompression;
        $this->assembler = $assembler;
        $this->uploader = $uploader;
    }

    /**
     * @Route("/upload", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return array
     */
    public function upload(Request $request)
    {
        ini_set('memory_limit', '1024M');

        $postedData = $this->dataCompression->decompress($request->getContent());
        $uploadCollection = $this->assembler->assembleFromArray($postedData);

        return $this->uploader->upload($uploadCollection);
    }
}

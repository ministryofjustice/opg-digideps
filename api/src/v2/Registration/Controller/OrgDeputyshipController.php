<?php

declare(strict_types=1);

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\v2\Controller\ControllerTrait;
use App\v2\Registration\Assembler\SiriusToOrgDeputyshipDtoAssembler;
use App\v2\Registration\Uploader\OrgDeputyshipUploader;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrgDeputyshipController extends AbstractController
{
    use ControllerTrait;
    const MAX_UPLOAD_BATCH_SIZE = 10000;

    /** @var OrgDeputyshipUploader */
    private $uploader;

    /** @var SiriusToOrgDeputyshipDtoAssembler */
    private $assembler;

    /** @var DataCompression */
    private $dataCompression;

    /**
     * OrgDeputyshipController constructor.
     */
    public function __construct(
        OrgDeputyshipUploader $orgDeputyshipUploader,
        SiriusToOrgDeputyshipDtoAssembler $assembler,
        DataCompression $dataCompression
    ) {
        $this->uploader = $orgDeputyshipUploader;
        $this->assembler = $assembler;
        $this->dataCompression = $dataCompression;
    }

    /**
     * @Route("/org-deputyships", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function create(Request $request)
    {
        $decompressedData = $this->dataCompression->decompress($request->getContent());
        $rowCount = count($decompressedData);

        if (!$rowCount) {
            throw new RuntimeException('No records received from the API');
        }
        if ($rowCount > self::MAX_UPLOAD_BATCH_SIZE) {
            throw new RuntimeException(sprintf('Max %s records allowed in a single bulk insert', self::MAX_UPLOAD_BATCH_SIZE));
        }

        $dtos = $this->assembler->assembleMultipleDtosFromArray($decompressedData);

        return $this->uploader->upload($dtos);
    }
}

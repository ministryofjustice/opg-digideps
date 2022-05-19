<?php

declare(strict_types=1);

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\Service\Formatter\RestFormatter;
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

    public const MAX_UPLOAD_BATCH_SIZE = 10000;

    public function __construct(
        private OrgDeputyshipUploader $uploader,
        private SiriusToOrgDeputyshipDtoAssembler $assembler,
        private DataCompression $dataCompression,
        private RestFormatter $restFormatter
    ) {
    }

    /**
     * @Route("/org-deputyships", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function create(Request $request)
    {
        $decompressedData = $this->dataCompression->decompress($request->getContent());
        $rowCount = count($decompressedData);

        $this->restFormatter->setJmsSerialiserGroups(['org-created-event']);

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

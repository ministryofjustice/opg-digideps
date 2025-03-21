<?php

declare(strict_types=1);

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\Service\Formatter\RestFormatter;
use App\v2\Controller\ControllerTrait;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrgDeputyshipController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private DataCompression $dataCompression,
        private CSVDeputyshipProcessing $csvProcessing,
        private RestFormatter $restFormatter
    ) {
    }

    #[Route(path: '/org-deputyships', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function create(Request $request)
    {
        $decompressedData = $this->dataCompression->decompress($request->getContent());

        $this->restFormatter->setJmsSerialiserGroups(['org-created-event']);

        return $this->csvProcessing->orgProcessing($decompressedData);
    }
}

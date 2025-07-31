<?php

declare(strict_types=1);

namespace App\v2\Registration\Controller;

use App\Service\DataCompression;
use App\Service\Formatter\RestFormatter;
use App\v2\Controller\ControllerTrait;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrgDeputyshipController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private readonly DataCompression $dataCompression,
        private readonly CSVDeputyshipProcessing $csvProcessing,
        private readonly RestFormatter $restFormatter
    ) {
    }

    #[Route(path: '/org-deputyships', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function create(Request $request): array
    {
        $decompressedData = $this->dataCompression->decompress($request->getContent());

        $this->restFormatter->setJmsSerialiserGroups(['org-created-event']);

        return $this->csvProcessing->orgProcessing($decompressedData);
    }
}

<?php

namespace App\v2\Registration\Controller;

use App\Entity\CasRec;
use App\Entity\User;
use App\Event\CSVUploadedEvent;
use App\Service\Audit\AuditEvents;
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
        $assembler = $this->factory->create($postedData);
        $uploadCollection = $assembler->assembleFromArray($postedData);

        $this->dispatchCSVUploadEvent($postedData);

        return $this->uploader->upload($uploadCollection);
    }

    private function dispatchCSVUploadEvent($postedData)
    {
        $source = CasRec::CASREC_SOURCE;

        if (CasRec::SIRIUS_SOURCE == $postedData[0]['Source']) {
            $source = CasRec::SIRIUS_SOURCE;
        }

        $csvUploadedEvent = new CSVUploadedEvent(
            $source,
            User::TYPE_LAY,
            AuditEvents::EVENT_CSV_UPLOADED
        );

        $this->eventDispatcher->dispatch($csvUploadedEvent, CSVUploadedEvent::NAME);
    }
}

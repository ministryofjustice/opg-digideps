<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\CasRecRepository;
use AppBundle\Entity\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/court-order")
 */
class CourtOrderController
{
    use ControllerTrait;

    /** @var ClientRepository */
    private $clientRepository;

    /** @var CasRecRepository */
    private $casRecRepository;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(ClientRepository $clientRepository, CasRecRepository $casRecRepository, EntityManagerInterface $em)
    {
        $this->clientRepository = $clientRepository;
        $this->casRecRepository = $casRecRepository;
        $this->em = $em;
    }

    /**
     * @Route("", methods={"POST"})
     * @Security("has_role('ROLE_LAY_DEPUTY')")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        /** @var Client $client */
        $client = $this->clientRepository->find($data['id']);

        /** @var Report $report */
        $report = $client->getCurrentReport();

        /** @var CasRec $registrationData */
        $registrationData = $this->casRecRepository->findOneBy(['caseNumber' => $client->getCaseNumber()]);

        $courtOrderType = strtolower($registrationData->getCorref()) === 'hw' ? CourtOrder::SUBTYPE_HW : CourtOrder::SUBTYPE_PFA;
        $courtOrder = new CourtOrder();

        $courtOrder
            ->setCaseNumber($client->getCaseNumber())
            ->setOrderDate($registrationData->getOrderDate())
            ->setType($courtOrderType)
            ->setClient($client)
            ->addReport($report);

        if (strtolower($registrationData->getTypeOfReport()) == 'opg102') {
            $courtOrder->setSupervisionLevel(CourtOrder::LEVEL_GENERAL);
        } else if (strtolower($registrationData->getTypeOfReport()) == 'opg103') {
            $courtOrder->setSupervisionLevel(CourtOrder::LEVEL_MINIMAL);
        }

        $report->setCourtOrder($courtOrder);
        $this->em->persist($courtOrder);
        $this->em->flush();

        return $this->buildSuccessResponse(['id' => $courtOrder->getId()], 'Court Order created', Response::HTTP_CREATED);
    }
}

<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\CasRecRepository;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\v2\Assembler\CourtOrder\LayToCourtOrderDtoAssembler;
use AppBundle\v2\DTO\CourtOrderDto;
use AppBundle\v2\Factory\CourtOrderFactory;
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

    /** @var LayToCourtOrderDtoAssembler */
    private $courtOrderAssembler;

    /** @var CourtOrderFactory */
    private $courtOrderFactory;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(
        ClientRepository $clientRepository,
        CasRecRepository $casRecRepository,
        LayToCourtOrderDtoAssembler $courtOrderAssembler,
        CourtOrderFactory $courtOrderFactory,
        EntityManagerInterface $em
    )
    {
        $this->clientRepository = $clientRepository;
        $this->casRecRepository = $casRecRepository;
        $this->courtOrderAssembler = $courtOrderAssembler;
        $this->courtOrderFactory = $courtOrderFactory;
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
        $client = $this->clientRepository->find($data['id']);
        $registrationData = $this->casRecRepository->findOneBy(['caseNumber' => $client->getCaseNumber()]);

        $courtOrderDto = $this->courtOrderAssembler->assemble($registrationData);
        $courtOrder = $this->courtOrderFactory->create($courtOrderDto, $client, $client->getCurrentReport());

        $this->em->persist($courtOrder);
        $this->em->flush();

        return $this->buildSuccessResponse(['id' => $courtOrder->getId()], 'Court Order created', Response::HTTP_CREATED);
    }
}

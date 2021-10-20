<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\ClientBenefitsCheckInterface;
use App\Entity\Report\Report;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\ClientBenefitsCheckRepository;
use App\Repository\NdrClientBenefitsCheckRepository;
use App\Service\Formatter\RestFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientBenefitsCheckController extends RestController
{
    private ClientBenefitsCheckRepository $clientBenefitsCheckRepository;
    private NdrClientBenefitsCheckRepository $ndrClientBenefitsCheckRepository;
    private ClientBenefitsCheckFactory $factory;
    private RestFormatter $formatter;

    public function __construct(
        ClientBenefitsCheckRepository $clientBenefitsCheckRepository,
        NdrClientBenefitsCheckRepository $ndrClientBenefitsCheckRepository,
        ClientBenefitsCheckFactory $factory,
        RestFormatter $formatter
    ) {
        $this->clientBenefitsCheckRepository = $clientBenefitsCheckRepository;
        $this->factory = $factory;
        $this->formatter = $formatter;
        $this->ndrClientBenefitsCheckRepository = $ndrClientBenefitsCheckRepository;
    }

    /**
     * @Route("/client-benefits-check/{reportOrNdr}", methods={"POST"}, name="persist"), requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * })))
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function create(Request $request, string $reportOrNdr)
    {
        $this->setJmsGroups($request);

        $clientBenefitsCheck = $this->factory->createFromFormData(json_decode($request->getContent(), true), $reportOrNdr);

        return $this->processEntity($clientBenefitsCheck, $reportOrNdr);
    }

    /**
     * @Route("/client-benefits-check/{reportOrNdr}/{id}", methods={"GET"}, name="read", requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * })))
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function read(Request $request, string $id, string $reportOrNdr)
    {
        $this->setJmsGroups($request);

        return 'ndr' === $reportOrNdr ? $this->ndrClientBenefitsCheckRepository->findBy(['id' => $id], ['created' => 'ASC']) :
            $this->clientBenefitsCheckRepository->findBy(['id' => $id], ['created' => 'ASC']);
    }

    /**
     * @Route("/client-benefits-check/{reportOrNdr}/{id}", methods={"PUT"}, name="update", requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * }))))
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function update(Request $request, $id, string $reportOrNdr)
    {
        $this->setJmsGroups($request);

        $existingEntity = $this->getCorrectRepository($reportOrNdr)->find($id);
        $clientBenefitsCheck = $this->factory->createFromFormData(
            json_decode($request->getContent(), true),
            $reportOrNdr,
            $existingEntity
        );

        return $this->processEntity($clientBenefitsCheck, $reportOrNdr);
    }

    private function processEntity(ClientBenefitsCheckInterface $clientBenefitsCheck, string $reportOrNdr)
    {
        $this->getCorrectRepository($reportOrNdr)->persistAndFlush($clientBenefitsCheck);

        if ($clientBenefitsCheck->getReport()) {
            $clientBenefitsCheck->getReport()->updateSectionsStatusCache([Report::SECTION_CLIENT_BENEFITS_CHECK]);
        }

        return $clientBenefitsCheck;
    }

    private function setJmsGroups(Request $request)
    {
        $groups = $request->get('groups') ? $request->get('groups') : ['client-benefits-check', 'report', 'ndr-client', 'ndr'];
        $this->formatter->setJmsSerialiserGroups($groups);
    }

    private function getCorrectRepository(string $reportOrNdr)
    {
        return 'ndr' === $reportOrNdr ? $this->ndrClientBenefitsCheckRepository : $this->clientBenefitsCheckRepository;
    }
}

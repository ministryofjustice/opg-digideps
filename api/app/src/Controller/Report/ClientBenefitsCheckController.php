<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\ClientBenefitsCheck as ReportClientBenefitsCheck;
use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Report\Report;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\ClientBenefitsCheckRepository;
use App\Repository\NdrClientBenefitsCheckRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClientBenefitsCheckController extends RestController
{
    public function __construct(
        private readonly ClientBenefitsCheckRepository $clientBenefitsCheckRepository,
        private readonly NdrClientBenefitsCheckRepository $ndrClientBenefitsCheckRepository,
        private readonly ClientBenefitsCheckFactory $factory,
        private readonly RestFormatter $formatter,
        EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/{reportOrNdr}/client-benefits-check', name: 'persist', requirements: ['reportOrNdr' => '(report|ndr)'], methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function create(Request $request, string $reportOrNdr): ReportClientBenefitsCheck|NdrClientBenefitsCheck
    {
        $this->setJmsGroups($request);

        $clientBenefitsCheck = $this->factory->createFromFormData(json_decode($request->getContent(), true), $reportOrNdr);

        return $this->processEntity($clientBenefitsCheck, $reportOrNdr);
    }

    #[Route(path: '/{reportOrNdr}/client-benefits-check/{id}', name: 'read', requirements: ['reportOrNdr' => '(report|ndr)'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function read(Request $request, string $id, string $reportOrNdr): array
    {
        $this->setJmsGroups($request);

        return 'ndr' === $reportOrNdr ? $this->ndrClientBenefitsCheckRepository->findBy(['id' => $id], ['created' => 'ASC']) :
            $this->clientBenefitsCheckRepository->findBy(['id' => $id], ['created' => 'ASC']);
    }

    #[Route(path: '/{reportOrNdr}/client-benefits-check/{id}', name: 'update', requirements: ['reportOrNdr' => '(report|ndr)'], methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function update(Request $request, string $id, string $reportOrNdr): ReportClientBenefitsCheck|NdrClientBenefitsCheck
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

    private function processEntity(
        ReportClientBenefitsCheck|NdrClientBenefitsCheck $clientBenefitsCheck,
        string $reportOrNdr
    ): ReportClientBenefitsCheck|NdrClientBenefitsCheck {
        $this->getCorrectRepository($reportOrNdr)->persistAndFlush($clientBenefitsCheck);

        $clientBenefitsCheck->getReport()?->updateSectionsStatusCache([Report::SECTION_CLIENT_BENEFITS_CHECK]);
        $this->getCorrectRepository($reportOrNdr)->persistAndFlush($clientBenefitsCheck);

        return $clientBenefitsCheck;
    }

    private function setJmsGroups(Request $request): void
    {
        $groups = $request->request->has('groups') ?
            $request->request->all('groups') : ['client-benefits-check', 'report', 'ndr-client', 'ndr'];
        $this->formatter->setJmsSerialiserGroups($groups);
    }

    private function getCorrectRepository(string $reportOrNdr): ClientBenefitsCheckRepository|NdrClientBenefitsCheckRepository
    {
        return 'ndr' === $reportOrNdr ? $this->ndrClientBenefitsCheckRepository : $this->clientBenefitsCheckRepository;
    }
}

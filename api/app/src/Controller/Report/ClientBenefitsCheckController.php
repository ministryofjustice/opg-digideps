<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\ClientBenefitsCheck as ReportClientBenefitsCheck;
use App\Entity\Report\Report;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\ClientBenefitsCheckRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClientBenefitsCheckController extends RestController
{
    public function __construct(
        private readonly ClientBenefitsCheckRepository $clientBenefitsCheckRepository,
        private readonly ClientBenefitsCheckFactory $factory,
        private readonly RestFormatter $formatter,
        EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/report/client-benefits-check', name: 'persist', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function create(Request $request): ReportClientBenefitsCheck
    {
        $this->setJmsGroups($request);

        $clientBenefitsCheck = $this->factory->createFromFormData(json_decode($request->getContent(), true));

        return $this->processEntity($clientBenefitsCheck);
    }

    #[Route(path: '/report/client-benefits-check/{id}', name: 'read', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function read(Request $request, string $id): array
    {
        $this->setJmsGroups($request);

        return $this->clientBenefitsCheckRepository->findBy(['id' => $id], ['created' => 'ASC']);
    }

    #[Route(path: '/report/client-benefits-check/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function update(Request $request, string $id): ReportClientBenefitsCheck
    {
        $this->setJmsGroups($request);

        $existingEntity = $this->clientBenefitsCheckRepository->find($id);
        $clientBenefitsCheck = $this->factory->createFromFormData(
            json_decode($request->getContent(), true),
            $existingEntity
        );

        return $this->processEntity($clientBenefitsCheck);
    }

    private function processEntity(ReportClientBenefitsCheck $clientBenefitsCheck): ReportClientBenefitsCheck
    {
        $this->clientBenefitsCheckRepository->persistAndFlush($clientBenefitsCheck);

        $clientBenefitsCheck->getReport()?->updateSectionsStatusCache([Report::SECTION_CLIENT_BENEFITS_CHECK]);
        $this->clientBenefitsCheckRepository->persistAndFlush($clientBenefitsCheck);

        return $clientBenefitsCheck;
    }

    private function setJmsGroups(Request $request): void
    {
        $groups = $request->request->has('groups') ? $request->request->all('groups') : ['client-benefits-check', 'report'];
        $this->formatter->setJmsSerialiserGroups($groups);
    }
}

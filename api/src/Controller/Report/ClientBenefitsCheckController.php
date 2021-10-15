<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Report;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\ClientBenefitsCheckRepository;
use App\Service\Formatter\RestFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientBenefitsCheckController extends RestController
{
    private ClientBenefitsCheckRepository $repository;
    private ClientBenefitsCheckFactory $factory;
    private RestFormatter $formatter;

    public function __construct(
        ClientBenefitsCheckRepository $repository,
        ClientBenefitsCheckFactory $factory,
        RestFormatter $formatter
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/client-benefits-check", methods={"POST"}, name="persist")
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function create(Request $request)
    {
        $this->setJmsGroups($request);

        $clientBenefitsCheck = $this->factory->createFromFormData(json_decode($request->getContent(), true));

        return $this->processEntity($clientBenefitsCheck);
    }

    /**
     * @Route("/client-benefits-check/{id}", methods={"GET"}, name="read")
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function read(Request $request, string $id)
    {
        $this->setJmsGroups($request);

        return $this->repository->findBy(['id' => $id], ['created' => 'ASC']);
    }

    /**
     * @Route("/client-benefits-check/{id}", methods={"PUT"}, name="update")
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function update(Request $request, $id)
    {
        $this->setJmsGroups($request);

        $existingEntity = $this->repository->find($id);
        $clientBenefitsCheck = $this->factory->createFromFormData(json_decode($request->getContent(), true), $existingEntity);

        return $this->processEntity($clientBenefitsCheck);
    }

    private function processEntity(ClientBenefitsCheck $clientBenefitsCheck)
    {
        $this->repository->persistAndFlush($clientBenefitsCheck);
        $clientBenefitsCheck->getReport()->updateSectionsStatusCache([Report::SECTION_CLIENT_BENEFITS_CHECK]);

        return $clientBenefitsCheck;
    }

    private function setJmsGroups(Request $request)
    {
        $groups = $request->get('groups') ? $request->get('groups') : ['client-benefits-check', 'report'];
        $this->formatter->setJmsSerialiserGroups($groups);
    }
}

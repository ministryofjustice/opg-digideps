<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Factory\ClientBenefitsCheckFactory;
use App\Repository\ClientBenefitsCheckRepository;
use App\Service\Formatter\RestFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientBenefitsCheckController extends RestController
{
    private ClientBenefitsCheckRepository $repository;
    private SerializerInterface $serializer;
    private ClientBenefitsCheckFactory $factory;
    private RestFormatter $formatter;

    public function __construct(ClientBenefitsCheckRepository $repository, ClientBenefitsCheckFactory $factory, RestFormatter $formatter)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/client-benefits-check", methods={"POST"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function create(Request $request)
    {
        $clientBenefitsCheck = $this->factory->createFromFormData(json_decode($request->getContent(), true));
        $this->repository->create($clientBenefitsCheck);

        $groups = $request->get('groups') ? $request->get('groups') : ['client-benefits-check', 'report'];
        $this->formatter->setJmsSerialiserGroups($groups);

        return $clientBenefitsCheck;
    }
}

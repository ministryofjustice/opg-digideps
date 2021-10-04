<?php

namespace App\Controller;

use App\Repository\CasRecRepository;
use App\Repository\ClientRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case")
 */
class CaseController extends RestController
{
    private ClientRepository $clientRepository;
    private CasRecRepository $casrecRepository;
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    public function __construct(ClientRepository $clientRepository, CasRecRepository $casRecRepository, EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->clientRepository = $clientRepository;
        $this->casrecRepository = $casRecRepository;
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/search-all", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function searchAllAction(Request $request)
    {
        $this->formatter->setJmsSerialiserGroups(['client']);

        $clients = $this->clientRepository->searchClients(
            $request->get('q'),
            $request->get('order_by'),
            $request->get('sort_order'),
            $request->get('limit'),
            $request->get('offset')
        );

        $formattedClients = [];

        foreach ($clients as $client) {
            $formattedClients[] =
                ['caseNumber' => $client->getCaseNumber(), 'clientSurname' => $client->getLastname()];
        }

        return $formattedClients;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CasRecRepository;
use App\Repository\ClientRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CourtOrderController extends RestController
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
     * @Route("court-order/search-all", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function searchAllAction(Request $request)
    {
        $courtOrders = $this->casrecRepository->searchForCourtOrders(
            $request->get('q'),
            $request->get('limit'),
        );

        return $courtOrders;
    }
}

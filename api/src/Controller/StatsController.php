<?php

namespace App\Controller;

use App\Service\Stats\StatsQueryParameters;
use App\Service\Stats\QueryFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class StatsController extends RestController
{
    private QueryFactory $QueryFactory;
    private EntityManager $em;

    public function __construct(QueryFactory $QueryFactory, EntityManagerInterface $em)
    {
        $this->QueryFactory = $QueryFactory;
        $this->em = $em;
    }

    /**
     * @Route("/stats", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getMetric(Request $request)
    {
        $params = new StatsQueryParameters($request->query->all());
        $query = $this->QueryFactory->create($params);

        return $query->execute($params);
    }

    /**
     * @Route("/stats/activeLays", methods={"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function getActiveLays()
    {
        // Get all lays that have logged in within last year
        $this->em->getRepository(User::class)->findOneBy(['']);
        // Get:
//        'userId'
//        'userFullName'
//        'userEmail'
//        'userPhoneNumber'
//        'reportsSubmitted'
//        'userRegisteredOn'
    }
}

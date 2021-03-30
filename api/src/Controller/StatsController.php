<?php declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Stats\StatsQueryParameters;
use App\Service\Stats\QueryFactory;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class StatsController extends RestController
{
    private QueryFactory $QueryFactory;
    private UserRepository $userRepository;

    public function __construct(
        QueryFactory $QueryFactory,
        UserRepository $userRepository
    ) {
        $this->QueryFactory = $QueryFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/stats", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getMetric(Request $request)
    {
        $params = new StatsQueryParameters($request->query->all());
        $query = $this->QueryFactory->create($params);

        return $query->execute($params);
    }

    /**
     * @Route("stats/deputies/lay/active", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getActiveLays()
    {
        return $this->userRepository->findActiveLaysInLastYear();
    }
}

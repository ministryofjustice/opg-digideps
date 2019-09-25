<?php

namespace AppBundle\Controller;

use AppBundle\Service\Stats\StatsQueryParameters;
use AppBundle\Service\Stats\MetricQueryFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class StatsController extends RestController
{
    /**
     * @Route("/stats")
     * @Method({"GET"})
     */
    public function getMetric(Request $request)
    {
        $params = new StatsQueryParameters($request->query->all());
        $query = (new MetricQueryFactory($this->getEntityManager()))->create($params);

        return $query->execute($params);
    }
}

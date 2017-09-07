<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\StatsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/stats")
 */
class StatsController extends RestController
{
    /**
     * @Route("/users")
     * @Method({"GET"})
     */
    public function users(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        $stats = $this->get('stats_service');
        /* @var $stats StatsService */
        $ret = $stats->getRecords($request->query->get('limit'));

        $this->get('kernel.listener.responseConverter')->addContextModifier(function ($context) {
            $context->setSerializeNull(true);
        });

        return $ret;
    }

    /**
     * Return CSV file created by crontab
     * @Route("/users.csv")
     * @Method({"GET"})
     */
    public function usersCsv(Request $request)
    {
        $response = new Response();
        $response->setContent(readfile('/tmp/stats.csv'));
        return $response;
    }
}

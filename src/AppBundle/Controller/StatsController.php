<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\StatsService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $stats = $this->get('app.service.stats_service');
        /* @var $stats StatsService */
        $ret = $stats->getRecords($request->query->get('limit'));

        $this->get('kernel.listener.responseConverter')->addContextModifier(function ($context) {
            $context->setSerializeNull(true);
        });

        return $ret;
    }

    /**
     * @Route("/users/csv/{timestamp}")
     * @Method({"GET"})
     */
    public function usersCsv(Request $request, $timestamp)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $file = '/tmp/stats' . $timestamp . '.csv';

        if (file_exists($file)) {
            echo file_get_contents($file);
            die;
        }

        exec("/usr/bin/php /app/app/console digideps:stats.csv $file &> /dev/null &");
        echo 'done';
    }
}

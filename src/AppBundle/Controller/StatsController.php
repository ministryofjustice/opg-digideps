<?php

namespace AppBundle\Controller;

use AppBundle\Service\StatsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

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

        $stats = $this->get('statsService'); /* @var $stats StatsService */
        $ret = $stats->getRecords($request->query->get('limit'));

        $this->get('kernel.listener.responseConverter')->addContextModifier(function ($context) {
            $context->setSerializeNull(true);
        });

        return $ret;
    }
}

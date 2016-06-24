<?php

namespace AppBundle\Controller\Odr;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity as EntityDir;
use AppBundle\Controller\RestController;

class OdrController extends RestController
{

    /**
     * @Route("/odr/{id}")
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $groups = $request->query->has('groups') ? (array)$request->query->get('groups') : ['odr'];
        $this->setJmsSerialiserGroups($groups);

        //$this->getRepository('Odr\Odr')->warmUpArrayCacheTransactionTypes();

        $report = $this->findEntityBy('Odr\Odr', $id);
        /* @var $report EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($report);

        return $report;
    }
}

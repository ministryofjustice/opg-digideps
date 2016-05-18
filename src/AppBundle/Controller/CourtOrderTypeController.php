<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/court-order-type")
 */
class CourtOrderTypeController extends RestController
{
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAllCourtOrderTypeAction()
    {
        $courtOrderTypes = $this->getDoctrine()->getManager()->getRepository('AppBundle:CourtOrderType')->findAll();

        return ['court_order_types' => $courtOrderTypes];
    }
}

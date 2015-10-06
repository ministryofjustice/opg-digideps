<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Exception as AppExceptions;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
* @Route("/court-order-type")
*/
class CourtOrderTypeController extends RestController
{
    /**
     * @Route("/all")
     */
    public function getAllCourtOrderTypeAction()
    {
        $courtOrderTypes = $this->getDoctrine()->getManager()->getRepository('AppBundle:CourtOrderType')->findAll();
        
        return [ 'court_order_types' => $courtOrderTypes ];
    }
}
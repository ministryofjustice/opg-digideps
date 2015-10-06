<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Exception as AppExceptions;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class IndexController extends RestController
{
    /**
     * @Route("/court-order-type/all")
     */
    public function getAllCourtOrderTypeAction()
    {
        $courtOrderTypes = $this->getDoctrine()->getManager()->getRepository('AppBundle:CourtOrderType')->findAll();
        
        return [ 'court_order_types' => $courtOrderTypes ];
    }
}
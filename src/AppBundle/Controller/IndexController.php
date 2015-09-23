<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Exception as AppExceptions;


class IndexController extends Controller
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
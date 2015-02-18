<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class IndexController extends Controller
{
    /**
     * @Route("/court-order-type/all")
     */
    public function getAllCourtOrderTypeAction()
    {
        $courtOrderTypes = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:CourtOrderType')->findAll();
        
        return [ 'court_order_types' => $courtOrderTypes ];
    }
}
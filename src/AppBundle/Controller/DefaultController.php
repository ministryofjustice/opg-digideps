<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        // depending on user data, redirect to specific page
        
        //return $this->redirect($this->generateUrl('user_details'));
//        return $this->render('AppBundle:default:index.html.twig');
    }
}

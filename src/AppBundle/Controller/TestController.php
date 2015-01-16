<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    /**
     * @Route("/test", name="homepage")
     */
    public function indexAction()
    {
        return new \Symfony\Component\HttpFoundation\Response("test");
    }
}

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
        $client = $this->get('restclient');
        $response = $client->get('http://symfony.com');
        print_r($response->getStatusCode()); die;
        return $this->render('default/index.html.twig');
    }
}

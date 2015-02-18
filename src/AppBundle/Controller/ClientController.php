<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/add")
     * @Method({"POST"})
     */
    public function addAction()
    {
        return [];
    }
}

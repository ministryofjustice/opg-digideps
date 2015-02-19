<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ClientType;
use AppBundle\Entity\Client;


/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/add", name="client_add")
     * @Template()
     */
    public function addAction()
    {
        $request = $this->getRequest();
        
        $form = $this->createForm(new ClientType(), new Client());
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                //print_r($form->getData()); die;
                die('valid');
            }
        }
        return [ 'form' => $form->createView() ];
    }
}
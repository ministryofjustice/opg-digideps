<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/admin")
*/
class AdminController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $restClient = $this->container->get('restclient');
        $serializer = $this->container->get('jms_serializer');
        
        $body = $restClient->get('list_users')->getBody();
        
        $arrayBody = $serializer->deserialize($body,'array','json')['data'];
       
        $users = array();
        foreach ($arrayBody as $userArray) { 
            $users[] = $serializer->deserialize(json_encode($userArray),'AppBundle\Entity\User','json');
        }
        
        return $this->render('AppBundle:Admin:index.html.twig', array('users'=>$users));
    }
    
    /**
     * @Route("/user")
     * @Method({"POST"})
     */
    public function addUserAction(Request $request)
    {
        $restClient = $this->container->get('restclient');
        $serializer = $this->container->get('jms_serializer');
        
        $body = json_encode(array(
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
        ));
        
        $restClient->post('add_user', ['body'=>$body]);
        
        return $this->redirect('/admin');
    }
}

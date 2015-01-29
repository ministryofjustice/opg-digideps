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
        $users = $this->get('apiClient')->getEntities('user', 'User');
        
        return $this->render('AppBundle:Admin:index.html.twig', array('users'=>$users));
    }
    
    /**
     * @Route("/user")
     * @Method({"POST"})
     */
    public function addUserAction(Request $request)
    {
        $this->get('apiClient')->post('add_user', array(
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
        ));
        
        return $this->redirect('/admin');
    }
}

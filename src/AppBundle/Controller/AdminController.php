<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Symfony\Component\Form\Form;

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
        $users = $this->get('apiclient')->getEntities('User', 'list_users');
        
        $form = $this->getAddForm();
        
        return $this->render('AppBundle:Admin:index.html.twig', array(
            'users'=>$users, 'form'=>$form->createView()
        ));
    }
    
    /**
     * @return Form
     */
    private function getAddForm()
    {
        return $this->createFormBuilder(new User)
            ->add('email', 'text')
            ->add('first_name', 'text')
            ->add('last_name', 'text')
            ->add('save', 'submit', array('label' => 'Add User'))
            ->setAction('/admin/user_add')
            ->getForm();
    }
    
    /**
     * @Route("/user_add")
     * @Method({"POST"})
     */
    public function addUserAction(Request $request)
    {
        $form = $this->getAddForm();
        
        $form->bind($request);
        $data = $this->get('jms_serializer')->serialize($form->getData(), 'json');
        
        $this->get('apiclient')->post('add_user', $data);
        
        return $this->redirect('/admin');
    }
}

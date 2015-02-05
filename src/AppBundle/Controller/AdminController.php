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
     * @Route("/", name="admin_homepage")
     */
    public function indexAction(Request $request)
    {
        $form = $this->getAddForm();
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get('apiclient')->post('add_user', $form->getData());

                return $this->redirect($this->generateUrl('admin_homepage'));
            }
        }
        
        return $this->render('AppBundle:admin:index.html.twig', array(
            'users'=>$this->get('apiclient')->getEntities('User', 'list_users'), 
            'form'=>$form->createView()
        ));
    }
    
    /**
     * @return Form
     */
    private function getAddForm()
    {
        // validation is in the User class (annotacion format)
        // to put int a class, validate form the builder directly http://symfony.com/doc/current/book/forms.html#adding-validation
        return $this->createFormBuilder(new User)
            ->add('email', 'text')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('save', 'submit', array('label' => 'Add User'))
            ->getForm();
    }
    
}

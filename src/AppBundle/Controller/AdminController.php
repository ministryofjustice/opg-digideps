<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Symfony\Component\Form\Form;
use AppBundle\Service\ApiClient;
use AppBundle\Service\MailSender;
use AppBundle\Form\AddUserType;

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
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        $roles = $this->get('apiclient')->getEntities('Role', 'list_roles');

        $form = $this->createForm(new AddUserType([
            'roles' => $roles,
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), new User());
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                $response = $apiClient->postC('add_user', $form->getData(), [
                    'deserialise_group' => 'admin_add_user'] //only serialise the properties modified by this form)
                );
                $user = $apiClient->getEntity('User', 'user/' . $response['id']);
                
                // mail activation link
                $mailSender = $this->get('mailSender'); /* @var $mailSender MailSender */
                $mailSender->sendUserActivationEmail($user);

                $request->getSession()->getFlashBag()->add(
                    'notice', 
                    'An activation email has been sent to the user.'
                );
                
                return $this->redirect($this->generateUrl('admin_homepage'));
            } 
        }
        
        return $this->render('AppBundle:Admin:index.html.twig', array(
            'users'=>$this->get('apiclient')->getEntities('User', 'list_users'), 
            'roles'=>$roles, 
            'form'=>$form->createView()
        ));
    }
}

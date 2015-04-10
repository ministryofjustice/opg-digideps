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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
* @Route("/admin")
*/
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        $form = $this->createForm(new AddUserType([
            'roles' => $this->get('apiclient')->getEntities('Role', 'list_roles'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), new User());
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                $response = $apiClient->postC('add_user', $form->getData(), [
                    'deserialise_group' => 'admin_add_user' //only serialise the properties modified by this form)
                ]);
                $user = $apiClient->getEntity('User', 'user/' . $response['id']);
                
                // send activation link
                $this->get('mailSender')->sendUserActivationEmail($user);

                $request->getSession()->getFlashBag()->add(
                    'notice', 
                    'An activation email has been sent to the user.'
                );
                
                return $this->redirect($this->generateUrl('admin_homepage'));
            } 
        }
        
        return [
            'users'=>$this->get('apiclient')->getEntities('User', 'list_users'), 
            'form'=>$form->createView()
        ];
    }
    
    /**
     * @Route("/edit-user/{id}", name="admin_editUser")
     * @Method({"GET", "POST"})
     * @Template
     * 
     * @param Request $request
     */
    public function editUserAction($id)
    {
        $apiClient = $this->get('apiclient');
        
        $user = $apiClient->getEntity('User','find_user_by_id', [ 'query' => [ $id ] ]);
       
        if(empty($user)){
            throw new \Exception('User does not exists');
        }
        
        $form = $this->createForm(new AddUserType([
            'roles' => $this->get('apiclient')->getEntities('Role', 'list_roles'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), $user );
        
        return [ 'form' => $form->createView(), 'action' => 'edit', 'id' => $id ];
    }
    
    /**
     * @Route("/delete-confirm/{id}", name="admin_delete_confirm")
     * @Method({"GET"})
     * @Template()
     * 
     * @param type $id
     */
    public function deleteConfirmAction($id)
    {
       $apiClient = $this->get('apiclient');
        
       $user = $apiClient->getEntity('User','find_user_by_id', [ 'query' => [ $id ] ]); 
       
       return [ 'user' => $user ];
    }
    
    /**
     * @Route("/delete/{id}", name="admin_delete")
     * @Method({"GET"})
     * @Template()
     * 
     * @param integer $id
     */
    public function deleteAction($id)
    {
        $apiClient = $this->get('apiclient');
        $apiClient->delete('delete_user_by_id',[ 'query' => ['adminId' => $this->getUser()->getId(), 'id' => $id ]]);
        
        return $this->redirect($this->generateUrl('admin_homepage'));
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\ApiClient;
use AppBundle\Service\MailSender;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        $orderBy = $request->query->has('order_by')? $request->query->get('order_by'): 'firstname';
        $sortOrder = $request->query->has('sort_order')? $request->query->get('sort_order'): 'ASC';

        
        $form = $this->createForm(new FormDir\AddUserType([
            'roles' => $this->get('apiclient')->getEntities('Role', 'list_roles'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), new EntityDir\User());
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                $response = $apiClient->postC('add_user', $form->getData(), [
                    'deserialise_group' => 'admin_add_user' //only serialise the properties modified by this form)
                ]);
                $user = $apiClient->getEntity('User', 'user/' . $response['id']);
                
                $activationEmail = $this->get('mailFactory')->createActivationEmail($user, 'activate');
                $this->get('mailSender')->send($activationEmail, [ 'text', 'html']);

                $request->getSession()->getFlashBag()->add(
                    'notice', 
                    'An activation email has been sent to the user.'
                );
                
                $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_ADD, $user);
                
                return $this->redirect($this->generateUrl('admin_homepage'));
            } 
        }

        $users = $this->get('apiclient')->getEntities('User', 'list_users', [ 'parameters' => [$orderBy, $sortOrder]]);
        $newSortOrder = $sortOrder == "ASC"? "DESC": "ASC";

        return [
            'users'=>$users, 
            'form'=>$form->createView(),
            'newSortOrder' => $newSortOrder
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
        $request = $this->getRequest();
        
        $user = $apiClient->getEntity('User','find_user_by_id', [ 'parameters' => [ $id ] ]);
       
        if(empty($user)){
            throw new \Exception('User does not exists');
        }
        
        $roleDisabled = false;
        $userRole = $user->getRole();
        

        if($userRole['role'] == "ROLE_ADMIN"){
            $roleDisabled = true;
        }

        $form = $this->createForm(new FormDir\AddUserType([
            'roles' => $this->get('apiclient')->getEntities('Role', 'list_roles'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin'),
            'roleDisabled' => $roleDisabled
        ]), $user );
    
        if($request->getMethod() == "POST"){
            $form->handleRequest($request);
            
            if($form->isValid()){
                $updateUser = $form->getData();
                $apiClient->putC('user/' . $user->getId(), $updateUser);
                
                $request->getSession()->getFlashBag()->add('action', 'action.message');
                
                $this->redirect($this->generateUrl('admin_editUser', [ 'id' => $user->getId() ]));
            }
        }
        
        return [ 'form' => $form->createView(), 'action' => 'edit', 'id' => $id, 'user' => $user ];
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
        
       $user = $apiClient->getEntity('User','find_user_by_id', [ 'parameters' => [ $id ] ]); 
       
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
        
        $user = $apiClient->getEntity('User','find_user_by_id', [ 'parameters' => [ $id ] ]); 
        
        $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_DELETE, $user);
        
        $apiClient->delete('delete_user_by_id',[ 'parameters' => ['adminId' => $this->getUser()->getId(), 'id' => $id ]]);
        
        return $this->redirect($this->generateUrl('admin_homepage'));
    }
}

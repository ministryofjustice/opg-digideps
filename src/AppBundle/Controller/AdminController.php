<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
/**
* @Route("/admin")
*/
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $orderBy = $request->query->has('order_by')? $request->query->get('order_by'): 'firstname';
        $sortOrder = $request->query->has('sort_order')? $request->query->get('sort_order'): 'ASC';

        
        $form = $this->createForm(new FormDir\AddUserType([
            'roles' => $this->get('restClient')->get('role', 'Role[]'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), new EntityDir\User());
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                $response = $restClient->post('user', $form->getData(), [
                    'deserialise_group' => 'admin_add_user' //only serialise the properties modified by this form)
                ]);
                $user = $restClient->get('user/' . $response['id'], 'User');

                $request->getSession()->getFlashBag()->add(
                    'notice', 
                    'An activation email has been sent to the user.'
                );
                
                $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_ADD, $user);
                
                return $this->redirect($this->generateUrl('admin_homepage'));
            } 
        }
        
        $limit = $request->query->get('limit') ?: 50;
        $offset = $request->query->get('offset') ?: 0;
        $userCount = $this->get('restClient')->get("user/count", 'array');
        $users = $this->get('restClient')->get("user/get-all/{$orderBy}/{$sortOrder}/$limit/$offset", 'User[]');
        $newSortOrder = $sortOrder == "ASC"? "DESC": "ASC";
        
        return [
            'users'=>$users, 
            'userCount'=> $userCount,
            'limit' => $limit,
            'offset' => $offset,
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
        $restClient = $this->get('restClient');
        $request = $this->getRequest();
        
        $user = $restClient->get("user/{$id}", 'User');
       
        if(empty($user)){
            throw new \Exception('User does not exists');
        }
        
        
        $form = $this->createForm(new FormDir\AddUserType([
            'roles' => $this->get('restClient')->get('role', 'Role[]'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), $user );
    
        if($request->getMethod() == "POST"){
            $form->handleRequest($request);
            
            if($form->isValid()){
                $updateUser = $form->getData();
                $restClient->put('user/' . $user->getId(), $updateUser);
                
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
       $restClient = $this->get('restClient');
        
       $user = $restClient->get("user/{$id}", 'User'); 
       
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
        $restClient = $this->get('restClient');
        
        $user = $restClient->get("user/{$id}", 'User'); 
        
        $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_DELETE, $user);
        
        $restClient->delete('user/' . $id);
        
        return $this->redirect($this->generateUrl('admin_homepage'));
    }
}

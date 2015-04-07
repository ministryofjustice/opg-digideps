<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
//use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent();

        $user = new \AppBundle\Entity\User();
        
        $this->populateUser($user, $data);
        
        /**
         * Not sure we need this check, email field is set as unique in the db. May be try catch the unique value exception
         * thrown when persist flush ?
         */
        if ($user->getEmail() && $this->getRepository('User')->findOneBy(['email'=>$user->getEmail()])) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.");
        }
        
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush($user);
        
         //TODO return status code
        
        return array('id'=>$user->getId());
    }
    
    
    
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function update($id)
    {
        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user User */

        $data = $this->deserializeBodyContent();
        
        $this->populateUser($user, $data);
        
        $this->getEntityManager()->flush($user);
        
        //TODO return status code
        
        return ['id'=>$user->getId()];
    }

    
    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function get($id)
    {
        return $this->findEntityBy('User', $id, 'User not found');
    }
    
    /**
     * 
     * @Route("/{adminId}/{id}")
     * @Method({"DELETE"})
     * 
     * @param integer $id
     * @return array []
     * @throws \RuntimeException
     */
    public function delete($id,$adminId)
    {
        $adminUser = $this->getRepository('User')->find($adminId);
        
        if(empty($adminUser) || ($adminUser->getRole()->getRole() != "ROLE_ADMIN") || ($adminId == $id)){
            throw new \RuntimeException("You are not authorized to perform this action");
        }
        
        $user = $this->getRepository('User')->find($id);
        
        if(empty($user)){
            throw new \RuntimeException("User not found");
        }
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
        
        return [];
    }

    
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        return $this->getRepository('User')->findAll();
    }

    
    /**
     * @Route("/get-by-email/{email}")
     * @Method({"GET"})
     */
    public function getByEmail($email)
    {
        return $this->findEntityBy('User', ['email'=> strtolower($email)], "User not found");
    }
    
    
    /**
     * @Route("/get-by-token/{token}")
     * @Method({"GET"})
     */
    public function getByToken($token)
    {
        return $this->findEntityBy('User', ['registrationToken'=>$token], "User not found");
    }
    
    /**
     * call setters on User when $data contains values
     * 
     * @param User $user
     * @param array $data
     */
    private function populateUser(User $user, array $data)
    {
        // Cannot easily(*) use JSM deserialising with already constructed objects. 
        // Also. It'd be possible to differentiate when a NULL value is intentional or not
        // (*) see options here https://github.com/schmittjoh/serializer/issues/79
        // http://jmsyst.com/libs/serializer/master/event_system
        
        $this->hydrateEntityWithArrayData($user, $data, [
            'firstname' => 'setFirstname', 
            'lastname' => 'setLastname', 
            'email' => 'setEmail', 
            'password' => 'setPassword', 
            'active' => 'setActive', 
            'address1' => 'setAddress1', 
            'address2' => 'setAddress2', 
            'address3' => 'setAddress3', 
            'address_postcode' => 'setAddressPostcode', 
            'address_country' => 'setAddressCountry', 
            'phone_home' => 'setPhoneHome', 
            'phone_work' => 'setPhoneWork', 
            'phone_mobile' => 'setPhoneMobile'
        ]);
        
        if (array_key_exists('role_id', $data)) {
            $role = $this->findEntityBy('Role', $data['role_id'], 'Role not found');
            $user->setRole($role);
        }
    }
    
}

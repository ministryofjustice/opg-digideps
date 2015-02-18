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

        // validate input
        foreach (['firstname', 'lastname', 'email', 'role_id'] as $k) {
            if (empty($data[$k])) {
                throw new \InvalidArgumentException("Missing parameter $k");
            }
        }
        
        $role = $this->findEntityBy('Role', $data['role_id'], 'Role not found');
        
        $user = new \AppBundle\Entity\User();
        $user->setFirstname($data['firstname'])
                ->setLastname($data['lastname'])
                ->setEmail($data['email'])
                ->setRole($role);
        
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
        
        if (array_key_exists('password', $data)) {
            $user->setPassword($data['password']);
        }
        if (array_key_exists('active', $data)) {
            $user->setActive((bool)$data['active']);
        }
        
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
        return $this->findEntityBy('User', ['email'=>$email], "User not found");
    }
    
    
    /**
     * @Route("/get-by-token/{token}")
     * @Method({"GET"})
     */
    public function getByToken($token)
    {
        return $this->findEntityBy('User', ['registrationToken'=>$token], "User not found");
    }
    
}

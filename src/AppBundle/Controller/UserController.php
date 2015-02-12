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
        array_map(function($k) use ($data) {
            if (!array_key_exists($k, $data)) {
                throw new \InvalidArgumentException("Missing parameter $k");
            }
        }, ['firstname', 'lastname', 'email']);
        
        
        
        $user = new \AppBundle\Entity\User();
        $user->setFirstname($data['firstname'])
                ->setLastname($data['lastname'])
                ->setEmail($data['email']);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush($user);
        
         //TODO return status code
        
        return array('id'=>$user->getId());
    }
    
    
    /**
     * @Route("/{id}/set-password")
     * @Method({"PUT"})
     */
    public function setPassword($id)
    {
        $user = $this->findEntityById('User', $id, 'User not found'); /* @var $user User */
        
        $data = $this->deserializeBodyContent();
        
         // validate input
        array_map(function($k) use ($data) {
            if (!array_key_exists($k, $data)) {
                throw new \InvalidArgumentException("Missing parameter $k");
            }
        }, ['password']);
        
        $user->setPassword($data['password']);
        $user->setRegistrationToken('');
        
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
        return $this->findEntityById('User', $id, 'User not found');
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
        $user = $this->getRepository('User')->findOneByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }


        return $user;
    }
    
    
    /**
     * @Route("/get-by-token/{token}")
     * @Method({"GET"})
     */
    public function getByToken($token)
    {
        $user = $this->getRepository('User')->findOneBy(['registrationToken' => $token]);
        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user;
    }
    
    
}

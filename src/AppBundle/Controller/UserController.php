<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
//use FOS\RestBundle\Controller\FOSRestController;
//use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function getAll()
    {
        $em = $this->getDoctrine()->getManager();
        
        $serializer = $this->container->get('serializer');
        
        $users = $em->getRepository('AppBundle\Entity\User')->findAll();
        
        //TODO do not hardcode JSON encoding, move to controller 
        return new Response($serializer->serialize($users, 'json'));
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function get($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle\Entity\User')->find($id);
        if (!$user) {
            throw new \Exception('not found');
        }
        
        $serializer = $this->container->get('serializer');
        
        //TODO do not hardcode JSON encoding, move to controller 
        return new Response($serializer->serialize($user, 'json'));
    }
    
    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        $post = json_decode($request->getContent(), true);
        
        $em = $this->getDoctrine()->getManager();
        
        $user = new \AppBundle\Entity\User();
        $user->setFirstname($post['first_name']);
        $user->setLastname($post['last_name']);
        $user->setEmail($post['email']);
        $user->setPassword('');
        $em->persist($user);
        $em->flush($user);
        
        //CREATE user
        
        return array('id'=>$user->getId());
    }
}

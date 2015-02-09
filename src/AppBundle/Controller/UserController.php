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

        $em = $this->getDoctrine()->getManager();

        $user = new \AppBundle\Entity\User();
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setEmail($data['email']);
        $user->setPassword('');
        $em->persist($user);
        $em->flush($user);
        
        return array('id'=>$user->getId());
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

        return $user;
    }

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle\Entity\User')->findAll();

        return $users;
    }

    /**
     * @Route("/get-by-email/{email}")
     * @Method({"GET"})
     */
    public function getByEmail($email)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $em->getRepository('AppBundle\Entity\User')->findOneByEmail($email);
        if (!$user) {
            throw new \Exception('User not found');
        }


        return $user;
    }
}

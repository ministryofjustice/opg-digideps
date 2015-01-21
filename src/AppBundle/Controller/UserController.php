<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * @Route("/user")
 */
class UserController extends FOSRestController
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function listAction()
    {
//        $em = $this->getDoctrine()->getManager();
//
//        $users = $em->getRepository('AppBundle\Entity\User')->findAll();
        
        $data = array('elvis','paul'); // get data, in this case list of users.
        
        return $data;
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function listOneAction($id)
    {
        switch ($id) {
            case 1:
                return 'elvis';
        }
        
        throw new \AppBundle\Exception\NotFound('Only implemented with /user/1');
    }
    
    /**
     * @Route("/")
     * @Method({"POST"})
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     */
    public function addAction()
    {
        throw new \Exception("to implement");
    }
    
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function editAction()
    {
        throw new \Excepion("to implement");
    }
 
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteAction()
    {
        throw new \Excepion("to implement");
    }
}

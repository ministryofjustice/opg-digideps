<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;

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
        // find user by ID, return it
        
        switch ($id) {
            case 1:
                return 'elvis';
        }
        
        throw new \AppBundle\Exception\NotFound('Only implemented with /user/1');
    }
    
    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $post = json_decode($request->getContent(), true);
        
        //CREATE user
        
        return array('id'=>999, 'debug'=>$post);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function editAction(Request $request, $id)
    {
        $post = json_decode($request->getContent(), true);
        
        //find by id, update properties, flush
        
        return array('id'=>$id);
    }
 
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteAction($id)
    {
        //find by $id
        //delete
        
        return array('id'=>$id);
    }
}

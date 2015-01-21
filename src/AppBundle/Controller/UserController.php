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
        $data = array('elvis','paul'); // get data, in this case list of users.
        $view = $this->view($data, 200)
                ->setData($data)
            ->setTemplate("user/list.html.twig")
            ->setTemplateVar('users')
        ;

        return $this->handleView($view);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
//    public function listOneAction($id)
//    {
//        return new JsonResponse(array('method'=>__METHOD__, 'id'=>$id));
//    }
    
    /**
     * @Route("/")
     * @Method({"POST"})
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     */
//    public function addAction()
//    {
//        return new JsonResponse(array('method'=>__METHOD__));
//    }
    
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
//    public function editAction()
//    {
//        return new JsonResponse(array('method'=>__METHOD__));
//    }
//    
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
//    public function deleteAction()
//    {
//        return new JsonResponse(array('method'=>__METHOD__));
//    }
}

<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class RestController extends Controller
{
   
    
    /**
     * @return array
     */
    protected function getBodyContentAsArray()
    {
        return $this->container->get('kernel.listener.responseConverter')->requestBodyToArray($this->getRequest());
        
        
        
    }
}

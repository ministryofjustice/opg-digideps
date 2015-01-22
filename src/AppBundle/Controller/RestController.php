<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class RestController extends Controller
{
    /**
     * @return array
     */
    protected function deserializeBodyContent()
    {
        if ($this->container->has('kernel.listener.responseConverter')) {
            return $this->container->get('kernel.listener.responseConverter')->requestContentToArray($this->getRequest());
        }
        
        return $this->getRequest()->getContent();
    }
}

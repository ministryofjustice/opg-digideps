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
        $format = $this->getRequest()->getContentType();
        
        return $this->container->get('serializer')
                ->deserialize($this->getRequest()->getContent(), 'array', $format);
    }
}

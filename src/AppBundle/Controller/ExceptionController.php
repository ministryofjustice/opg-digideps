<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

class ExceptionController extends FOSRestController
{
    public function handleException($exception)
    {
        return array(
            'success' => false,
            'exception' => $exception->getMessage()
        );
    }
}

<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

class ExceptionController extends RestController
{
    public function handleException($exception)
    {
        return array(
            'success' => false,
            'exception' => $exception->getMessage()
        );
    }
}

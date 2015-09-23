<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Role;
use AppBundle\Exception as AppExceptions;



/**
 * @Route("/role")
 */
class RoleController extends RestController
{
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        return $this->getRepository('Role')->findAll();
    }

}

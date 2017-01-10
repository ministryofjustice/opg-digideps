<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Role;

/**
 * @Route("/role")
 */
class RoleController extends RestController
{
    /**
     * //TODO consider hardcoding roles in model, no need to store them in the DB
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        return $this->getRepository('Role')->findAll();
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/role")
 */
class RoleController extends RestController
{
    /**
     * //TODO consider hardcoding roles in model, no need to store them in the DB
     *
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll()
    {
        return $this->getRepository(Role::class)->findAll();
    }
}

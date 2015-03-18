<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;


class AccountController extends RestController
{
   /**
     * @Route("/report/find-account-by-id/{id}")
     * @Method({"GET"})
     */
    public function get($id)
    {
        $ret = $this->findEntityBy('Account', $id, 'Account not found');

        return $ret;
    }
}
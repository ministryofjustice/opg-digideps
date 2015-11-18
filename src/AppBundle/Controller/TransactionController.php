<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;


class TransactionController extends RestController
{
    /**
     * @Route("/report/{reportId}/transactions")
     * @Method({"GET"})
     */
    public function getAll()
    {

    }

    /**
     * @Route("/report/{reportId}/transactions")
     * @Method({"PUT"})
     */
    public function bulkEditAction($id)
    {

    }

}
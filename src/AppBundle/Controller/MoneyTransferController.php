<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;

class ReportController extends RestController
{
    /**
     * @Route("/report/{id}/money-transfers")
     * @Method({"GET"})
     */
    public function getAccountsAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array)$request->query->get('groups'));
        }
        
        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        $accounts = $this->getRepository('Account')->findByReport($report, [
            'id' => 'DESC'
        ]);
        
        if(count($accounts) === 0){
            return [];
        }
        return $accounts;
    }

}

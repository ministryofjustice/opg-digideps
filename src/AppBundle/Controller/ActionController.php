<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class ActionController extends RestController
{
    /**
     * @Route("/report/{reportId}/action")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $action = $report->getAction();
        if (!$action) {
            $action = new EntityDir\Action($report);
            $this->getEntityManager()->persist($action);
        }

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $action);

        $this->getEntityManager()->flush($action);

        return ['id' => $action->getId()];
    }

    /**
     * @Route("/report/{reportId}/action")
     * @Method({"GET"})
     * 
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $action = $this->findEntityBy('Action', $id, 'Action with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($action->getReport());

        return $action;
    }

    /**
     * @param array            $data
     * @param EntityDir\Action $action
     * 
     * @return \AppBundle\Entity\Report $report
     */
    private function updateEntity(array $data, EntityDir\Action $action)
    {
        if (array_key_exists('do_you_expect_financial_decisions', $data)) {
            $action->setDoYouExpectFinancialDecisions($data['do_you_expect_financial_decisions']);
        }

        if (array_key_exists('do_you_expect_financial_decisions_details', $data)) {
            $action->setDoYouExpectFinancialDecisionsDetails($data['do_you_expect_financial_decisions_details']);
        }

        if (array_key_exists('do_you_have_concerns', $data)) {
            $action->setDoYouHaveConcerns($data['do_you_have_concerns']);
        }

        if (array_key_exists('do_you_have_concerns_details', $data)) {
            $action->setDoYouHaveConcernsDetails($data['do_you_have_concerns_details']);
        }

        $action->cleanUpUnusedData();

        return $action;
    }
}

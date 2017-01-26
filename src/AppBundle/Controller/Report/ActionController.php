<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ActionController extends RestController
{
    /**
     * @Route("/report/{reportId}/action")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $action = $report->getAction();
        if (!$action) {
            $action = new EntityDir\Report\Action($report);
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

        $action = $this->findEntityBy('Report\Action', $id, 'Action with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($action->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['action'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $action;
    }

    /**
     * @param array            $data
     * @param EntityDir\Report\Action $action
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateEntity(array $data, EntityDir\Report\Action $action)
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

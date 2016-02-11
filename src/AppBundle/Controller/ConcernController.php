<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/concern")
 */
class ConcernController extends RestController
{

    /**
     * @Route("/concern")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $concern = new EntityDir\Concern();
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report', $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $concern->setReport($report);

        $this->updateConcernInfo($data, $concern);

        $this->persistAndFlush($concern);

        return ['id' => $concern->getId()];
    }

    /**
     * @Route("/concern/{id}")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $concern = $this->findEntityBy('Concern', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($concern->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateConcernInfo($data, $concern);

        $this->getEntityManager()->flush($concern);

        return ['id' => $concern->getId()];
    }

    /**
     * @Route("/{reportId}/concerns")
     * @Method({"GET"})
     *
     * @param integer $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->getRepository('Concern')->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/concern/{id}")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialiseGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : [ 'basic'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $concern = $this->findEntityBy('Concern', $id, "Concern with id:" . $id . " not found");
        $this->denyAccessIfReportDoesNotBelongToUser($concern->getReport());

        return $concern;
    }

    /**
     * @Route("/concern/{id}")
     * @Method({"DELETE"})
     */
    public function deleteConcern($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $concern = $this->findEntityBy('Concern', $id, 'Concern not found');
        $this->denyAccessIfReportDoesNotBelongToUser($concern->getReport());

        $this->getEntityManager()->remove($concern);
        $this->getEntityManager()->flush($concern);

        return [];
    }

    /**
     * @param array $data
     * @param EntityDir\Concern $concern
     * 
     * @return \AppBundle\Entity\Report $report
     */
    private function updateConcernInfo(array $data, EntityDir\Concern $concern)
    {
        if (array_key_exists('do_you_expect_financial_decisions', $data)) {
            $concern->setDoYouExpectFinancialDecisions($data['do_you_expect_financial_decisions']);
        }

        if (array_key_exists('do_you_have_concerns', $data)) {
            $concern->setDoYouHaveConcerns($data['do_you_have_concerns']);
        }

        return $concern;
    }

}

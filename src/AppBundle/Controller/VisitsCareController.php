<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/report")
 */
class VisitsCareController extends RestController
{
    /**
     * @Route("/visits-care")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $visitsCare = new EntityDir\VisitsCare();
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report', $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $visitsCare->setReport($report);
        $this->updateInfo($data, $visitsCare);

        $this->persistAndFlush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/visits-care/{id}")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $visitsCare = $this->findEntityBy('VisitsCare', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateInfo($data, $visitsCare);

        $this->getEntityManager()->flush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/{reportId}/visits-care")
     * @Method({"GET"})
     *
     * @param int $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->getRepository('VisitsCare')->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/visits-care/{id}")
     * @Method({"GET"})
     * 
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['visits-care'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy('VisitsCare', $id, 'VisitsCare with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        return $visitsCare;
    }

    /**
     * @Route("/visits-care/{id}")
     * @Method({"DELETE"})
     */
    public function deleteVisitsCare($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $visitsCare = $this->findEntityBy('VisitsCare', $id, 'VisitsCare not found');
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $this->getEntityManager()->remove($visitsCare);
        $this->getEntityManager()->flush($visitsCare);

        return [];
    }

    /**
     * @param array                  $data
     * @param EntityDir\VisitsCare $visitsCare
     * 
     * @return \AppBundle\Entity\Report $report
     */
    private function updateInfo(array $data, EntityDir\VisitsCare $visitsCare)
    {
        if (array_key_exists('do_you_live_with_client', $data)) {
            $visitsCare->setDoYouLiveWithClient($data['do_you_live_with_client']);
        }

        if (array_key_exists('does_client_receive_paid_care', $data)) {
            $visitsCare->setDoesClientReceivePaidCare($data['does_client_receive_paid_care']);
        }

        if (array_key_exists('how_often_do_you_contact_client', $data)) {
            $visitsCare->setHowOftenDoYouContactClient($data['how_often_do_you_contact_client']);
        }

        if (array_key_exists('how_is_care_funded', $data)) {
            $visitsCare->setHowIsCareFunded($data['how_is_care_funded']);
        }

        if (array_key_exists('who_is_doing_the_caring', $data)) {
            $visitsCare->setWhoIsDoingTheCaring($data['who_is_doing_the_caring']);
        }

        if (array_key_exists('does_client_have_a_care_plan', $data)) {
            $visitsCare->setDoesClientHaveACarePlan($data['does_client_have_a_care_plan']);
        }

        if (array_key_exists('when_was_care_plan_last_reviewed', $data)) {
            if (!empty($data['when_was_care_plan_last_reviewed'])) {
                $visitsCare->setWhenWasCarePlanLastReviewed(new \DateTime($data['when_was_care_plan_last_reviewed']));
            } else {
                $visitsCare->setWhenWasCarePlanLastReviewed(null);
            }
        }

        return $visitsCare;
    }
}

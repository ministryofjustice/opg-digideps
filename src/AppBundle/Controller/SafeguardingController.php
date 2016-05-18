<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/report")
 */
class SafeguardingController extends RestController
{
    /**
     * @Route("/safeguarding")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $safeguarding = new EntityDir\Safeguarding();
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report', $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $safeguarding->setReport($report);

        $this->updateSafeguardingInfo($data, $safeguarding);

        $this->persistAndFlush($safeguarding);

        return ['id' => $safeguarding->getId()];
    }

    /**
     * @Route("/safeguarding/{id}")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $safeguarding = $this->findEntityBy('Safeguarding', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($safeguarding->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateSafeguardingInfo($data, $safeguarding);

        $this->getEntityManager()->flush($safeguarding);

        return ['id' => $safeguarding->getId()];
    }

    /**
     * @Route("/{reportId}/safeguardings")
     * @Method({"GET"})
     *
     * @param int $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->getRepository('Safeguarding')->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/safeguarding/{id}")
     * @Method({"GET"})
     * 
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialiseGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['basic'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $safeguarding = $this->findEntityBy('Safeguarding', $id, 'Safeguarding with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($safeguarding->getReport());

        return $safeguarding;
    }

    /**
     * @Route("/safeguarding/{id}")
     * @Method({"DELETE"})
     */
    public function deleteSafeguarding($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $safeguarding = $this->findEntityBy('Safeguarding', $id, 'Safeguarding not found');
        $this->denyAccessIfReportDoesNotBelongToUser($safeguarding->getReport());

        $this->getEntityManager()->remove($safeguarding);
        $this->getEntityManager()->flush($safeguarding);

        return [];
    }

    /**
     * @param array                  $data
     * @param EntityDir\Safeguarding $safeguarding
     * 
     * @return \AppBundle\Entity\Report $report
     */
    private function updateSafeguardingInfo(array $data, EntityDir\Safeguarding $safeguarding)
    {
        if (array_key_exists('do_you_live_with_client', $data)) {
            $safeguarding->setDoYouLiveWithClient($data['do_you_live_with_client']);
        }

        if (array_key_exists('does_client_receive_paid_care', $data)) {
            $safeguarding->setDoesClientReceivePaidCare($data['does_client_receive_paid_care']);
        }

        if (array_key_exists('how_often_do_you_contact_client', $data)) {
            $safeguarding->setHowOftenDoYouContactClient($data['how_often_do_you_contact_client']);
        }

        if (array_key_exists('how_is_care_funded', $data)) {
            $safeguarding->setHowIsCareFunded($data['how_is_care_funded']);
        }

        if (array_key_exists('who_is_doing_the_caring', $data)) {
            $safeguarding->setWhoIsDoingTheCaring($data['who_is_doing_the_caring']);
        }

        if (array_key_exists('does_client_have_a_care_plan', $data)) {
            $safeguarding->setDoesClientHaveACarePlan($data['does_client_have_a_care_plan']);
        }

        if (array_key_exists('when_was_care_plan_last_reviewed', $data)) {
            if (!empty($data['when_was_care_plan_last_reviewed'])) {
                $safeguarding->setWhenWasCarePlanLastReviewed(new \DateTime($data['when_was_care_plan_last_reviewed']));
            } else {
                $safeguarding->setWhenWasCarePlanLastReviewed(null);
            }
        }

        return $safeguarding;
    }
}

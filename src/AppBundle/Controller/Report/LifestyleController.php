<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class LifestyleController extends RestController
{
    /**
     * @Route("/lifestyle")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $lifestyle = new EntityDir\Report\Lifestyle();
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $lifestyle->setReport($report);
        $this->updateInfo($data, $lifestyle);

        $this->persistAndFlush($lifestyle);

        return ['id' => $lifestyle->getId()];
    }

    /**
     * @Route("/lifestyle/{id}")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateInfo($data, $lifestyle);

        $this->getEntityManager()->flush($lifestyle);

        return ['id' => $lifestyle->getId()];
    }

    /**
     * @Route("/{reportId}/lifestyle")
     * @Method({"GET"})
     *
     * @param int $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->getRepository(EntityDir\Report\Lifestyle::class)->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/lifestyle/{id}")
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['lifestyle'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id, 'Lifestyle with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        return $lifestyle;
    }

    /**
     * @Route("/lifestyle/{id}")
     * @Method({"DELETE"})
     */
    public function deleteLifestyle($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id, 'VisitsCare not found');
        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        $this->getEntityManager()->remove($lifestyle);
        $this->getEntityManager()->flush($lifestyle);

        return [];
    }

    /**
     * @param array                       $data
     * @param EntityDir\Report\Lifestyle  $lifestyle
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateInfo(array $data, EntityDir\Report\Lifestyle $lifestyle)
    {
        if (array_key_exists('care_appointments', $data)) {
            $lifestyle->setCareAppointments($data['care_appointments']);
        }

        if (array_key_exists('does_client_undertake_social_activities', $data)) {
            $lifestyle->setDoesClientUndertakeSocialActivities($data['does_client_undertake_social_activities']);

            if ($data['does_client_undertake_social_activities'] === 'yes') {
                $lifestyle->setActivityDetails($data['activity_details']);
            } else {
                $lifestyle->setActivityDetails(null);
            }
        }

        return $lifestyle;
    }
}

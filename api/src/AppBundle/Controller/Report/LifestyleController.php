<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class LifestyleController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_LIFESTYLE];

    /**
     * @Route("/lifestyle")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAction(Request $request)
    {
        $lifestyle = new EntityDir\Report\Lifestyle();
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $lifestyle->setReport($report);
        $this->updateInfo($data, $lifestyle);

        $this->getEntityManager()->persist($lifestyle);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $lifestyle->getId()];
    }

    /**
     * @Route("/lifestyle/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $id)
    {
        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id);
        $report = $lifestyle->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateInfo($data, $lifestyle);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $lifestyle->getId()];
    }

    /**
     * @Route("/{reportId}/lifestyle")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->getRepository(EntityDir\Report\Lifestyle::class)->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/lifestyle/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
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
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteLifestyle($id)
    {
        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id, 'VisitsCare not found');
        $report = $lifestyle->getReport();

        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        $this->getEntityManager()->remove($lifestyle);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @param array                      $data
     * @param EntityDir\Report\Lifestyle $lifestyle
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateInfo(array $data, EntityDir\Report\Lifestyle $lifestyle)
    {
        if (array_key_exists('care_appointments', $data)) {
            $lifestyle->setCareAppointments($data['care_appointments']);
        }

        if (array_key_exists('does_client_undertake_social_activities', $data)) {
            $yesNo = $data['does_client_undertake_social_activities'];
            $lifestyle->setDoesClientUndertakeSocialActivities($yesNo);
            $lifestyle->setActivityDetailsYes($yesNo === 'yes' ?  $data['activity_details_yes'] : null);
            $lifestyle->setActivityDetailsNo($yesNo === 'no' ?  $data['activity_details_no'] : null);
        }

        return $lifestyle;
    }
}

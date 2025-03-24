<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report")
 */
class VisitsCareController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_VISITS_CARE];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    /**
     * @Route("/visits-care", methods={"POST"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function addAction(Request $request)
    {
        $visitsCare = new EntityDir\Report\VisitsCare();
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $visitsCare->setReport($report);
        $this->updateInfo($data, $visitsCare);

        $this->em->persist($visitsCare);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/visits-care/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id);
        $report = $visitsCare->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateInfo($data, $visitsCare);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/{reportId}/visits-care", methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     *
     * @param int $reportId
     */
    public function findByReportIdAction($reportId)
    {
        $this->formatter->setJmsSerialiserGroups(['visits-care']);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $ret = $this->em->getRepository(EntityDir\Report\VisitsCare::class)->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/visits-care/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['visits-care'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id, 'VisitsCare with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        return $visitsCare;
    }

    /**
     * @Route("/visits-care/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function deleteVisitsCare($id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id, 'VisitsCare not found');
        $report = $visitsCare->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $this->em->remove($visitsCare);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    /**
     * @return \App\Entity\Report\Report $report
     */
    private function updateInfo(array $data, EntityDir\Report\VisitsCare $visitsCare)
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

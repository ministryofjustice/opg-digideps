<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MentalCapacityController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_DECISIONS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
    }

    /**
     * @Route("/report/{reportId}/mental-capacity", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $mc = $report->getMentalCapacity();
        if (!$mc) {
            $mc = new EntityDir\Report\MentalCapacity($report);
            $this->em->persist($mc);
        }

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $mc);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $mc->getId()];
    }

    /**
     * @Route("/report/{reportId}/mental-capacity", methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $mc = $this->findEntityBy(EntityDir\Report\MentalCapacity::class, $id, 'MentalCapacity with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($mc->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['mental-capacity'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $mc;
    }

    /**
     * @return \App\Entity\Report\Report $report
     */
    private function updateEntity(array $data, EntityDir\Report\MentalCapacity $mc)
    {
        if (array_key_exists('has_capacity_changed', $data)) {
            $mc->setHasCapacityChanged($data['has_capacity_changed']);
        }

        if (array_key_exists('has_capacity_changed_details', $data)) {
            $mc->setHasCapacityChangedDetails($data['has_capacity_changed_details']);
        }

        if (array_key_exists('mental_assessment_date', $data)) {
            $mc->setMentalAssessmentDate(new \DateTime($data['mental_assessment_date']));
        }

        $mc->cleanUpUnusedData();

        return $mc;
    }
}

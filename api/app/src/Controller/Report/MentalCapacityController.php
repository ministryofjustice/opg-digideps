<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\MentalCapacity;
use App\Entity\Report\Report;
use App\Service\Formatter\RestFormatter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MentalCapacityController extends RestController
{
    private array $sectionIds = [Report::SECTION_DECISIONS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/mental-capacity', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function update(Request $request, int $reportId): array
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $mc = $report->getMentalCapacity();
        if (!$mc) {
            $mc = new MentalCapacity($report);
            $this->em->persist($mc);
        }

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $mc);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $mc->getId()];
    }

    #[Route(path: '/report/{reportId}/mental-capacity', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): MentalCapacity
    {
        $mc = $this->findEntityBy(MentalCapacity::class, $id, 'MentalCapacity with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($mc->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['mental-capacity'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $mc;
    }

    private function updateEntity(array $data, MentalCapacity $mc): MentalCapacity
    {
        if (array_key_exists('has_capacity_changed', $data)) {
            $mc->setHasCapacityChanged($data['has_capacity_changed']);
        }

        if (array_key_exists('has_capacity_changed_details', $data)) {
            $mc->setHasCapacityChangedDetails($data['has_capacity_changed_details']);
        }

        if (array_key_exists('mental_assessment_date', $data)) {
            $mc->setMentalAssessmentDate(new DateTime($data['mental_assessment_date']));
        }

        $mc->cleanUpUnusedData();

        return $mc;
    }
}

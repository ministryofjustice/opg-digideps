<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/report')]
class LifestyleController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_LIFESTYLE];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/lifestyle', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request): array
    {
        $lifestyle = new EntityDir\Report\Lifestyle();
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $lifestyle->setReport($report);
        $this->updateInfo($data, $lifestyle);

        $this->em->persist($lifestyle);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $lifestyle->getId()];
    }

    #[Route(path: '/lifestyle/{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function update(Request $request, int $id): array
    {
        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id);
        $report = $lifestyle->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateInfo($data, $lifestyle);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $lifestyle->getId()];
    }

    #[Route(path: '/{reportId}/lifestyle', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function findByReportId(int $reportId): array
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        return $this->em->getRepository(EntityDir\Report\Lifestyle::class)->findByReport($report);
    }

    #[Route(path: '/lifestyle/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): EntityDir\Report\Lifestyle
    {
        $serialiseGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['lifestyle'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id, 'Lifestyle with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        return $lifestyle;
    }

    #[Route(path: '/lifestyle/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteLifestyle(int $id): array
    {
        $lifestyle = $this->findEntityBy(EntityDir\Report\Lifestyle::class, $id, 'VisitsCare not found');
        $report = $lifestyle->getReport();

        $this->denyAccessIfReportDoesNotBelongToUser($lifestyle->getReport());

        $this->em->remove($lifestyle);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateInfo(array $data, EntityDir\Report\Lifestyle $lifestyle): void
    {
        if (array_key_exists('care_appointments', $data)) {
            $lifestyle->setCareAppointments($data['care_appointments']);
        }

        if (array_key_exists('does_client_undertake_social_activities', $data)) {
            $yesNo = $data['does_client_undertake_social_activities'];
            $lifestyle->setDoesClientUndertakeSocialActivities($yesNo);
            $lifestyle->setActivityDetailsYes('yes' === $yesNo ? $data['activity_details_yes'] : null);
            $lifestyle->setActivityDetailsNo('no' === $yesNo ? $data['activity_details_no'] : null);
        }
    }
}

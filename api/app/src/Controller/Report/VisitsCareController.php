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
class VisitsCareController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_VISITS_CARE];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/visits-care', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request): array
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

    #[Route(path: '/visits-care/{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function update(Request $request, int $id): array
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

    #[Route(path: '/{reportId}/visits-care', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function findByReportId(int $reportId): array
    {
        $this->formatter->setJmsSerialiserGroups(['visits-care']);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        return $this->em->getRepository(EntityDir\Report\VisitsCare::class)->findByReport($report);
    }

    #[Route(path: '/visits-care/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): EntityDir\Report\VisitsCare
    {
        $serialiseGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['visits-care'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id, 'VisitsCare with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        return $visitsCare;
    }

    #[Route(path: '/visits-care/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteVisitsCare(int $id): array
    {
        $visitsCare = $this->findEntityBy(EntityDir\Report\VisitsCare::class, $id, 'VisitsCare not found');
        $report = $visitsCare->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($visitsCare->getReport());

        $this->em->remove($visitsCare);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateInfo(array $data, EntityDir\Report\VisitsCare $visitsCare): void
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
    }
}

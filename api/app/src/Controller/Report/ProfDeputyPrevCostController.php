<?php

namespace OPG\Digideps\Backend\Controller\Report;

use OPG\Digideps\Backend\Controller\RestController;
use OPG\Digideps\Backend\Entity\Report\ProfDeputyPreviousCost;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfDeputyPrevCostController extends RestController
{
    private array $sectionIds = [Report::SECTION_PROF_DEPUTY_COSTS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/prof-deputy-previous-cost', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_PROF')]
    public function add(Request $request, int $reportId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        if (!array_key_exists('amount', $data)) {
            throw new \InvalidArgumentException('Missing amount');
        }
        $cost = new ProfDeputyPreviousCost($report, $data['amount']);
        $this->updateEntity($data, $cost);
        $report->setProfDeputyCostsHasPrevious('yes');

        $this->em->persist($cost);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $cost->getId()];
    }

    #[Route(path: '/prof-deputy-previous-cost/{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_PROF')]
    public function update(Request $request, int $id): array
    {
        $cost = $this->findEntityBy(ProfDeputyPreviousCost::class, $id);
        $report = $cost->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $cost);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $cost->getId()];
    }

    #[Route(path: '/prof-deputy-previous-cost/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_PROF')]
    public function getOneById(Request $request, int $id): ProfDeputyPreviousCost
    {
        $serialiseGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['prof-deputy-costs-prev'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $cost = $this->findEntityBy(ProfDeputyPreviousCost::class, $id, 'Prof Service Fee with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        return $cost;
    }

    #[Route(path: '/report/{reportId}/prof-deputy-previous-cost/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_PROF')]
    public function deleteProfDeputyPreviousCost(int $id): array
    {
        $cost = $this->findEntityBy(ProfDeputyPreviousCost::class, $id, 'Prof Service fee not found');
        $report = $cost->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        $this->em->remove($cost);
        $this->em->flush();

        if (0 === count($report->getProfDeputyPreviousCosts())) {
            $report->setProfDeputyCostsHasPrevious(null);
        }

        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateEntity(array $data, ProfDeputyPreviousCost $cost): ProfDeputyPreviousCost
    {
        if (array_key_exists('start_date', $data)) {
            $cost->setStartDate(new \DateTime($data['start_date']));
        }

        if (array_key_exists('end_date', $data)) {
            $cost->setEndDate(new \DateTime($data['end_date']));
        }

        if (array_key_exists('amount', $data)) {
            $cost->setAmount($data['amount']);
        }

        return $cost;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Satisfaction;
use App\Repository\ReportRepository;
use App\Repository\SatisfactionRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/satisfaction')]
class SatisfactionController extends RestController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RestFormatter $formatter,
        private readonly ReportRepository $reportRepository,
        private readonly SatisfactionRepository $satisfactionRepository
    ) {
        parent::__construct($em);
    }

    private function addSatisfactionScore(int $satisfactionLevel, string $comments): Satisfaction
    {
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($satisfactionLevel);
        $satisfaction->setComments($comments);

        $this->em->persist($satisfaction);
        $this->em->flush();

        return $satisfaction;
    }

    private function updateSatisfactionScore(Satisfaction $satisfaction, int $satisfactionLevel, string $comments): Satisfaction
    {
        $satisfaction->setScore($satisfactionLevel);
        $satisfaction->setComments($comments);

        $this->em->persist($satisfaction);
        $this->em->flush();

        return $satisfaction;
    }

    #[Route(path: '', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request): int
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'comments' => 'mustExist',
            'reportType' => 'notEmpty',
        ]);

        $report = $this->reportRepository->find($data['reportId']);

        /** @var ?Satisfaction $satisfaction */
        $satisfaction = $this->satisfactionRepository->findOneBy(['report' => $report]);

        if (is_null($satisfaction)) {
            $satisfaction = $this->addSatisfactionScore($data['score'], $data['comments']);
        } else {
            $satisfaction = $this->updateSatisfactionScore($satisfaction, intval($data['score']), $data['comments']);
        }

        $satisfaction->setReport($report);
        $satisfaction->setReportType($data['reportType']);
        $satisfaction->setDeputyRole($this->getUser()->getRoleName());

        $this->em->persist($satisfaction);
        $this->em->flush();

        return $satisfaction->getId();
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/satisfaction_data', name: 'satisfaction_data', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function getSatisfactionData(Request $request): array
    {
        /* @var $repo SatisfactionRepository */
        $repo = $this->em->getRepository(Satisfaction::class);

        $fromDate = $this->convertDateStringToDateTime($request->get('fromDate', ''));
        if (!is_null($fromDate)) {
            $fromDate->setTime(0, 0, 1);
        }

        $toDate = $this->convertDateStringToDateTime($request->get('toDate', ''));
        if (!is_null($toDate)) {
            $toDate->setTime(23, 59, 59);
        }

        return $repo->findAllSatisfactionSubmissions(
            $fromDate,
            $toDate,
        );
    }

    #[Route(path: '/public', methods: ['POST'])]
    public function publicAdd(Request $request): int
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'comments' => 'notEmpty',
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'], $data['comments']);

        return $satisfaction->getId();
    }
}

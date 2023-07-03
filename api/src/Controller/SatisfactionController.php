<?php

namespace App\Controller;

use App\Entity\Satisfaction;
use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Entity as EntityDir;

/**
 * @Route("/satisfaction")
 */
class SatisfactionController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;
    private ReportRepository $reportRepository;
    private NdrRepository $ndrRepository;

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter, ReportRepository $reportRepository, NdrRepository $ndrRepository)
    {
        $this->em = $em;
        $this->formatter = $formatter;
        $this->reportRepository = $reportRepository;
        $this->ndrRepository = $ndrRepository;
    }

    /**
     * @param string $satisfactionLevel
     * @param string $comments
     * @return Satisfaction
     */
    private function addSatisfactionScore(string $satisfactionLevel, string $comments)
    {
        $satisfaction = new Satisfaction();
        $satisfaction->setScore($satisfactionLevel);
        $satisfaction->setComments($comments);

        $this->em->persist($satisfaction);
        $this->em->flush();

        return $satisfaction;
    }

    /**
     * @Route("", methods={"POST"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function add(Request $request)
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'comments' => 'mustExist',
            'reportType' => 'notEmpty',
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'], $data['comments']);

        if ('ndr' === $data['reportType']) {
            $ndr = $this->ndrRepository->find($data['ndrId']);
            $satisfaction->setNdr($ndr);
        } else {
            $report = $this->reportRepository->find($data['reportId']);
            $satisfaction->setReport($report);
        }

        $satisfaction->setReportType($data['reportType']);
        $satisfaction->setDeputyRole($this->getUser()->getRoleName());

        $this->em->persist($satisfaction);
        $this->em->flush();

        return $satisfaction->getId();
    }

    /**
     * @Route("/satisfaction_data", name="satisfaction_data", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getSatisfactionData(Request $request)
    {
        /* @var $repo EntityDir\Repository\SatisfactionRepository */
        $repo = $this->getRepository(EntityDir\Satisfaction::class);

        $fromDate = $this->convertDateStringToDateTime($request->get('fromDate', ''));
        $fromDate instanceof DateTime ? $fromDate->setTime(0, 0, 1) : null;

        $toDate = $this->convertDateStringToDateTime($request->get('toDate', ''));
        $toDate instanceof DateTime ? $toDate->setTime(23, 59, 59) : null;

        return $repo->findAllSatisfactionSubmissions(
            $fromDate,
            $toDate,
            $request->get('orderBy', 'createdAt'),
            $request->get('order', 'ASC')
        );
    }

    /**
     * @Route("/public", methods={"POST"})
     */
    public function publicAdd(Request $request)
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'score' => 'notEmpty',
            'comments' => 'notEmpty'
        ]);

        $satisfaction = $this->addSatisfactionScore($data['score'], $data['comments']);

        return $satisfaction->getId();
    }
}

<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportSubmissionDownloadFilterType;
use AppBundle\Form\Admin\SatisfactionFilterType;
use AppBundle\Form\Admin\StatPeriodType;
use AppBundle\Mapper\ReportSatisfaction\ReportSatisfactionSummaryMapper;
use AppBundle\Mapper\ReportSatisfaction\ReportSatisfactionSummaryQuery;
use AppBundle\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use AppBundle\Mapper\ReportSubmission\ReportSubmissionSummaryQuery;
use AppBundle\Service\Client\RestClient;
use AppBundle\Transformer\ReportSubmission\ReportSubmissionBurFixedWidthTransformer;
use AppBundle\Transformer\ReportSubmission\SatisfactionTransformer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/stats")
 */
class StatsController extends AbstractController
{
    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        RestClient $restClient
    )
    {
        $this->restClient = $restClient;
    }

    /**
     * @Route("", name="admin_stats")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Stats:stats.html.twig")
     *
     * @param Request $request
     * @param ReportSubmissionSummaryMapper $mapper
     * @param ReportSubmissionBurFixedWidthTransformer $transformer
     *
     * @return array|Response
     */
    public function statsAction(Request $request, ReportSubmissionSummaryMapper $mapper, ReportSubmissionBurFixedWidthTransformer $transformer)
    {
        $form = $this->createForm(ReportSubmissionDownloadFilterType::class , new ReportSubmissionSummaryQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSubmissionSummaries = $mapper->getBy($form->getData());
                $downloadableData = $transformer->transform($reportSubmissionSummaries);

                return $this->buildResponse($downloadableData);

            } catch (\Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/satisfaction", name="admin_satisfaction")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Template("AppBundle:Admin/Stats:satisfaction.html.twig")
     * @param Request $request
     * @param ReportSatisfactionSummaryMapper $mapper
     * @return array|Response
     */
    public function satisfactionAction(Request $request, ReportSatisfactionSummaryMapper $mapper)
    {
        $form = $this->createForm(SatisfactionFilterType::class , new ReportSatisfactionSummaryQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSatisfactionSummaries = $mapper->getBy($form->getData());

                $spreadsheet = $this->createSatisfactionSpreadsheet($reportSatisfactionSummaries);

                $this->downloadSpreadsheet($spreadsheet);


            } catch (\Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @param $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSpreadsheet($spreadsheet)
    {
        $extension = 'Xlsx';
        $fileName = sprintf('Satisfaction_%s.xlsx', date('YmdHi'));
        $writer = IOFactory::createWriter($spreadsheet, $extension);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        $writer->save('php://output');
        exit();
    }

    /**
     * @param $reportSatisfactionSummaries
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createSatisfactionSpreadsheet($reportSatisfactionSummaries)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("Digideps")
        ->setLastModifiedBy("Digideps Application")
        ->setTitle("Satisfaction Report")
        ->setSubject("Satisfaction Report")
        ->setDescription(
            "Output of a particular date range of satisfaction entries."
        )
        ->setKeywords("Openxml php")
        ->setCategory("Satisfaction report results file");
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Score');
        $sheet->setCellValue('C1', 'Created Date');
        $sheet->setCellValue('D1', 'Comments');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(70);
        $headerRows = 'A1:D1';
        $sheet->getStyle($headerRows)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('525252');
        $sheet->getStyle($headerRows)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle($headerRows)->getFont()->setBold(true);

        $currentCell = 2;
        foreach ($reportSatisfactionSummaries as $reportSubmissionSummary) {
            $sheet->setCellValue("A{$currentCell}", $reportSubmissionSummary->getId());
            $sheet->setCellValue("B{$currentCell}", $reportSubmissionSummary->getScore());
            $sheet->setCellValue("C{$currentCell}", $reportSubmissionSummary->getCreated());
            $sheet->getStyle("C{$currentCell}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
            $sheet->setCellValue("D{$currentCell}", $reportSubmissionSummary->getComments());
            $currentCell++;
        }
        $sheet->getStyle("A1:D{$currentCell}")
            ->getAlignment()->setWrapText(true);

        $sheet->setAutoFilter("B1:B{$currentCell}");

        return $spreadsheet;
    }

    /**
     * @param $csvContent
     * @return Response
     */
    private function buildResponse($csvContent)
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $attachmentName = sprintf('cwsdigidepsopg00001%s.dat', date('YmdHi'));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        $response->sendHeaders();

        return $response;
    }

    /**
     * @Route("/metrics", name="admin_metrics")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Stats:metrics.html.twig")
     * @param Request $request
     * @return array|Response
     */
    public function metricsAction(Request $request)
    {
        $form = $this->createForm(StatPeriodType::class);
        $form->handleRequest($request);

        $append = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $append = "&startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
        }

        $metrics = ['satisfaction', 'reportsSubmitted', 'clients', 'registeredDeputies'];

        foreach ($metrics as $metric) {
            $all = $this->restClient->get('stats?metric=' . $metric . $append, 'array');
            $byRole = $this->restClient->get('stats?metric=' . $metric . '&dimension[]=deputyType' . $append, 'array');

            $stats[$metric] = array_merge(
                ['all' => $all[0]['amount']],
                $this->mapToDeputyType($byRole)
            );
        }

        return [
            'stats' => $stats,
            'form' => $form->createView()
        ];
    }

    /**
     * Map an array of metric responses to be addressible by deputyType
     *
     * @param array $result
     *
     * @return array
     */
    private function mapToDeputyType(array $result): array {
        $resultByDeputyType = [];

        foreach ($result as $resultBit) {
            $resultByDeputyType[$resultBit['deputyType']] = $resultBit['amount'];
        }

        return $resultByDeputyType;
    }
}

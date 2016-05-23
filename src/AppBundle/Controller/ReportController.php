<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity as EntityDir;

class ReportController extends RestController
{
    /**
     * @Route("/report")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $reportData = $this->deserializeBodyContent($request);

        // new report
        if (empty($reportData['client']['id'])) {
            throw new \InvalidArgumentException('Missing client.id');
        }
        $client = $this->findEntityBy('Client', $reportData['client']['id']);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $report = new EntityDir\Report();
        $report->setClient($client);

        // add court order type
        $courtOrderType = $this->findEntityBy('CourtOrderType', $reportData['court_order_type_id']);
        $report->setCourtOrderType($courtOrderType);

        $this->validateArray($reportData, [
            'start_date' => 'notEmpty',
            'end_date' => 'notEmpty',
        ]);

        // add other stuff
        $report->setStartDate(new \DateTime($reportData['start_date']));
        $report->setEndDate(new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);

        $this->persistAndFlush($report);

        return ['report' => $report->getId()];
    }

    /**
     * @Route("/report/{id}")
     * @Method({"GET"})
     * 
     * @param int $id
     */
    public function getById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $groups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['basic'];
        $this->setJmsSerialiserGroups($groups);

        $this->getRepository('Report')->warmUpArrayCacheTransactionTypes();

        $report = $this->findEntityBy('Report', $id); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        return $report;
    }

    /**
     * @Route("/report/{id}/submit")
     * @Method({"PUT"})
     */
    public function submit(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $currentReport = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $currentReport EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);
        $user = $this->getUser();
        $client = $currentReport->getClient();

        $data = $this->deserializeBodyContent($request);

        if (empty($data['submit_date'])) {
            throw new \InvalidArgumentException('Missing submit_date');
        }

        if (empty($data['agreed_behalf_deputy'])) {
            throw new \InvalidArgumentException('Missing agreed_behalf_deputy');
        }

        $currentReport->setAgreedBehalfDeputy($data['agreed_behalf_deputy']);
        if ($data['agreed_behalf_deputy'] === 'more_deputies_not_behalf') {
            $currentReport->setAgreedBehalfDeputyExplanation($data['agreed_behalf_deputy_explanation']);
        } else {
            $currentReport->setAgreedBehalfDeputyExplanation(null);
        }

//        if (!empty($data['reason_not_all_agreed'])) {
//            $currentReport->setAllAgreed(false);
//            $currentReport->setReasonNotAllAgreed($data['reason_not_all_agreed']);
//        } else {
//            $currentReport->setAllAgreed(true);
//        }

        $currentReport->setSubmitted(true);
        $currentReport->setSubmitDate(new \DateTime($data['submit_date']));

        // send report if submitted
        $reportContent = $this->forward('AppBundle:Report:pdf', ['reportId' => $currentReport->getId()])->getContent();

        $reportEmail = $this->getMailFactory()->createReportEmail($user, $client, $reportContent);
        $this->getMailSender()->send($reportEmail, ['html'], 'secure-smtp');

        //lets create subsequent year's report
        $nextYearReport = $this->getRepository('Report')->createNextYearReport($currentReport);

        //send confirmation email
        $reportConfirmEmail = $this->getMailFactory()->createReportSubmissionConfirmationEmail($user, $currentReport, $nextYearReport);
        $this->getMailSender()->send($reportConfirmEmail, ['text', 'html']);

        $this->getEntityManager()->flush($currentReport);

        //response to pass back
        return ['newReportId' => $nextYearReport->getId()];
    }

    /**
     * @Route("/report/{reportId}/formatted/{addLayout}")
     * @Method({"GET"})
     */
    public function formattedAction($reportId, $addLayout)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->getRepository('Report')->find($reportId); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $template = $addLayout
                  ? 'AppBundle:Report:formatted.html.twig'
                  : 'AppBundle:Report:formatted_body.html.twig';

        return $this->render($template, [
                'report' => $report,
                'client' => $report->getClient(),
                'assets' => $report->getAssets(),
                'groupAssets' => $report->getAssetsGroupedByType(),
                'contacts' => $report->getContacts(),
                'decisions' => $report->getDecisions(),
                'isEmailAttachment' => true,
                'deputy' => $report->getClient()->getUsers()->first(),
                'transfers' => $report->getMoneyTransfers(),
        ]);
    }

    /**
     * @Route("/report/{reportId}/pdf")
     * @Method({"GET"})
     */
    public function pdfAction($reportId)
    {
        try {
            $html = $this->forward('AppBundle:Report:formatted', array(
                'reportId' => $reportId,
                'addLayout' => true,
            ))->getContent();

            $pdf = $this->get('wkhtmltopdf')->getPdfFromHtml($html);

            $response = new Response($pdf);
            $response->headers->set('Content-Type', 'application/pdf');

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @Route("/report/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $this->getRepository('Report')->warmUpArrayCacheTransactionTypes();

        $report = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request);

        foreach (['transactions_in', 'transactions_out'] as $tk) {
            if (!isset($data[$tk])) {
                continue;
            }
            foreach ($data[$tk] as $transactionRow) {
                $t = $report->getTransactionByTypeId($transactionRow['id']); /* @var $t EntityDir\Transaction */
                if (!$t instanceof EntityDir\Transaction) {
                    continue;
                }
                $t->setAmounts($transactionRow['amounts'] ?: []);
                if (array_key_exists('more_details', $transactionRow)) {
                    $t->setMoreDetails($transactionRow['more_details']);
                }
                $this->getEntityManager()->flush($t);
            }
            $this->setJmsSerialiserGroups(['transactions']);
        }

        if (array_key_exists('cot_id', $data)) {
            $cot = $this->findEntityBy('CourtOrderType', $data['cot_id']);
            $report->setCourtOrderType($cot);
        }

        if (array_key_exists('start_date', $data)) {
            $report->setStartDate(new \DateTime($data['start_date']));
        }

        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
        }

        if (array_key_exists('reviewed', $data)) {
            $report->setReviewed((boolean) $data['reviewed']);
        }

        if (array_key_exists('report_seen', $data)) {
            $report->setReportSeen((boolean) $data['report_seen']);
        }

        if (array_key_exists('reason_for_no_contacts', $data)) {
            $report->setReasonForNoContacts($data['reason_for_no_contacts']);
        }

        if (array_key_exists('no_asset_to_add', $data)) {
            $report->setNoAssetToAdd($data['no_asset_to_add']);
        }

        if (array_key_exists('no_transfers_to_add', $data)) {
            $report->setNoTransfersToAdd($data['no_transfers_to_add']);
        }

        if (array_key_exists('reason_for_no_decisions', $data)) {
            $report->setReasonForNoDecisions($data['reason_for_no_decisions']);
        }

        if (array_key_exists('further_information', $data)) {
            $report->setFurtherInformation($data['further_information']);
        }

        if (array_key_exists('balance_mismatch_explanation', $data)) {
            $report->setBalanceMismatchExplanation($data['balance_mismatch_explanation']);
        }

        $this->getEntityManager()->flush($report);

        return ['id' => $report->getId()];
    }
}

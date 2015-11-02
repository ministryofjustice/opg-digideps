<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;

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

        if (!empty($reportData['id'])) {
            // get existing report
            $report = $this->findEntityBy('Report', $reportData['id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
        } else {
            // new report
            $client = $this->findEntityBy('Client', $reportData['client']);
            $this->denyAccessIfClientDoesNotBelongToUser($client);

            $report = new EntityDir\Report();
            $report->setClient($client);
        }

        // add court order type
        $courtOrderType = $this->findEntityBy('CourtOrderType', $reportData['court_order_type']);
        $report->setCourtOrderType($courtOrderType);

        // add other stuff
        $report->setStartDate(new \DateTime($reportData['start_date']));
        $report->setEndDate(new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);

        $this->persistAndFlush($report);

        return [ 'report' => $report->getId()];
    }

    /**
     * @Route("/report/{id}")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array) $request->query->get('groups'));
        }

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
            throw new \InvalidArgumentException("Missing submit_date");
        }

        if (!empty($data['dontAllAgreeReason'])) {
            $currentReport->setAllAgreed(false);
            $currentReport->setReasonNotAllAgreed($data['dontAllAgreeReason']);
        } else {
            $currentReport->setAllAgreed(true);
        }

        $currentReport->setSubmitted(true);
        $currentReport->setSubmitDate(new \DateTime($data['submit_date']));


        // send report if submitted
        $reportContent = $this->forward('AppBundle:Report:formatted', ['reportId' => $currentReport->getId()])->getContent();

        $reportEmail = $this->getMailFactory()->createReportEmail($user, $client, $reportContent);
        $this->getMailSender()->send($reportEmail, [ 'html'], 'secure-smtp');

        //lets create subsequent year's report
        $nextYearReport = $this->getRepository('Report')->createNextYearReport($currentReport);

        //send confirmation email
        $reportConfirmEmail = $this->getMailFactory()->createReportSubmissionConfirmationEmail($user, $currentReport, $nextYearReport);
        $this->getMailSender()->send($reportConfirmEmail, [ 'text', 'html']);

        //response to pass back
        return ['newReportId' => $nextYearReport->getId()];
    }

    public function formattedAction($reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->getRepository('Report')->find($reportId); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        return $this->render('AppBundle:Report:formatted.html.twig', [
                'report' => $report,
                'client' => $report->getClient(),
                'assets' => $report->getAssets(),
                'groupAssets' => $report->getAssetsGroupedByTitle(),
                'contacts' => $report->getContacts(),
                'decisions' => $report->getDecisions(),
                'isEmailAttachment' => true,
                'deputy' => $report->getClient()->getUsers()->first(),
        ]);
    }


    /**
     * @Route("/report/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request);

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

        if (array_key_exists('reason_for_no_decisions', $data)) {
            $report->setReasonForNoDecisions($data['reason_for_no_decisions']);
        }

        if (array_key_exists('further_information', $data)) {
            $report->setFurtherInformation($data['further_information']);
        }


        $this->getEntityManager()->flush($report);

        return ['id' => $report->getId()];
    }

}
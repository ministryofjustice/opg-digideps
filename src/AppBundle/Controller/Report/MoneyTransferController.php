<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MoneyTransferController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/money-transfers", name="money_transfers")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['money-transfer']);

        if (count($report->getMoneyTransfers()) > 0 || $report->getNoTransfersToAdd()) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/exist", name="money_transfers_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['money-transfer']);
        $form = $this->createForm(new FormDir\Report\MoneyTransferExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($report->getNoTransfersToAdd()) {
                case false:
                    return $this->redirectToRoute('money_transfer_add', ['reportId' => $reportId]);
                case true:
                    $this->get('restClient')->put('report/' . $reportId, $report, ['money-transfers-no-transfers']);
                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('money_transfers', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'summary') {
            $backLink = $this->generateUrl('money_transfers_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/add", name="money_transfer_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $contact = new EntityDir\Report\Contact();

        $form = $this->createForm(new FormDir\Report\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            // update contact. The API will also delete reason for no contact
            $this->getRestClient()->post('report/contact', $data, ['money-transfer', 'report-id']);

            return $this->redirect($this->generateUrl('money_transfer_add_another', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('money_transfers_exist', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'another') {
            $backLink = $this->generateUrl('money_transfer_add_another', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/money-transfers/add_another", name="money_transfer_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\Report\ContactAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_transfer_add', ['reportId' => $reportId, 'from'=>'another']);
                case 'no':
                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/money_transfers/edit/{contactId}", name="contact_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId, $contactId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $contact = $this->getRestClient()->get('report/contact/' . $contactId, 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(new FormDir\Report\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $request->getSession()->getFlashBag()->add('notice', 'Record edited');

            $this->getRestClient()->put('report/contact', $data);
            return $this->redirect($this->generateUrl('money_transfers', ['reportId' => $reportId]));

        }

        return [
            'backLink' => $this->generateUrl('money_transfers_summary', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/money-transfers/summary", name="money_transfers_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['money-transfer']);
        //count($report->getMoneyTransfers()) > 0 || $report->getNoTransfersToAdd()

        return [
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/money-transfers/{contactId}/delete", name="contact_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $contactId)
    {
        $this->getRestClient()->delete("/report/contact/{$contactId}");

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Contact deleted'
        );

        return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
    }
}

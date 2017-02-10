<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    private static $jmsGroups = [
        'contact',
    ];

    /**
     * @Route("/report/{reportId}/contacts", name="contacts")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatusService()->getContactsState()['state'] != ReportStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/exist", name="contacts_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\ContactExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['hasContacts']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $report, ['reasonForNoContacts', 'contacts']);
                    foreach ($report->getContacts() as $contact) {
                        $this->getRestClient()->delete('/report/contact/' . $contact->getId());
                    }
                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('contacts', ['reportId'=>$reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('contacts_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/add", name="contacts_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = new EntityDir\Report\Contact();

        $form = $this->createForm(new FormDir\Report\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            // update contact. The API will also delete reason for no contact
                $this->getRestClient()->post('report/contact', $data, ['contact', 'report-id']);

            return $this->redirect($this->generateUrl('contacts_add_another', ['reportId' => $reportId]));
        }

        $backLinkRoute = 'contacts_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/add_another", name="contacts_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('report-contacts'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from'=>'add_another']);
                case 'no':
                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/edit/{contactId}", name="contacts_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId, $contactId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->getRestClient()->get('report/contact/' . $contactId, 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(new FormDir\Report\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $request->getSession()->getFlashBag()->add('notice', 'Contact edited');

            $this->getRestClient()->put('report/contact', $data);
            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('contacts_summary', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/summary", name="contacts_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatusService()->getContactsState()['state'] == ReportStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('contacts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/{contactId}/delete", name="contacts_delete")
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

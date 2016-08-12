<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/contacts", name="contacts")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function listAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['contact']);
        $contacts = $report->getContacts();

        if (empty($contacts) && $report->isDue() == false) {
            return $this->redirect($this->generateUrl('add_contact', ['reportId' => $reportId]));
        }

        return [
            'contacts' => $contacts,
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/add", name="add_contact")
     * @Template("AppBundle:Report/Contact:add.html.twig")
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
            $this->getRestClient()->post('report/contact', $data, [
                'deserialise_group' => 'Default',
            ]);

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/{id}/edit", name="edit_contact")
     * @Template("AppBundle:Report/Contact:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $contact = $this->getRestClient()->get('report/contact/'.$id, 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(new FormDir\Report\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('report/contact', $data);

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/{id}/delete", name="delete_contact")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        $this->getRestClient()->delete("/report/contact/{$id}");

        return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/contacts/delete-nonereason", name="delete_nonereason_contacts")
     */
    public function deleteReasonAction($reportId)
    {
        //just do some checks to make sure user is allowed to update this report
        $report = $this->getReport($reportId);

        if (!empty($report)) {
            $report->setReasonForNoContacts(null);
            $this->get('restClient')->put('report/'.$report->getId(), $report, [
                'deserialise_group' => 'reasonForNoContacts'
            ]);
        }

        return $this->redirect($this->generateUrl('contacts', ['reportId' => $report->getId()]));
    }

    /**
     * @Route("/report/{reportId}/contacts/nonereason", name="edit_contacts_nonereason")
     * @Template("AppBundle:Report/Contact:edit_none_reason.html.twig")
     */
    public function noneReasonAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\Report\ReasonForNoContactType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->get('restClient')->put('report/'.$reportId, $data, [
                'deserialise_group' => 'reasonForNoContacts'
            ]);

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * Sub controller action called when the no contact form is embedded in another page.
     *
     * @Template("AppBundle:Report\Contact:_none_reason_form.html.twig")
     */
    public function _noneReasonFormAction(Request $request, $reportId)
    {
        $actionUrl = $this->generateUrl('edit_contacts_nonereason', ['reportId' => $reportId]);
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $form = $this->createForm(new FormDir\Report\ReasonForNoContactType(), $report, ['action' => $actionUrl]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('restClient')->put('report/'.$reportId, $form->getData(), [
                'deserialise_group' => 'reasonForNoContacts'
            ]);
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }
}

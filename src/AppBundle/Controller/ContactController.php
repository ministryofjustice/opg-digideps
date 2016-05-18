<?php

namespace AppBundle\Controller;

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
     * @Template("AppBundle:Contact:list.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function listAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'contacts']);
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
     * @Template("AppBundle:Contact:add.html.twig")
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client']);

        $contact = new EntityDir\Contact();
        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            // update contact. The API will also delete reason for no contact
            $this->get('restClient')->post('report/contact', $data, [
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
     * @Template("AppBundle:Contact:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client']);

        $contact = $this->get('restClient')->get('report/contact/'.$id, 'Contact');
        $contact->setReport($report);

        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->get('restClient')->put('report/contact', $data);

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
        //just do some checks to make sure user is allowed to delete this contact
        $report = $this->getReport($reportId, ['basic', 'contacts']);

        foreach ($report->getContacts() as $contact) {
            if ($contact->getId() == $id) {
                $this->get('restClient')->delete("/report/contact/{$id}");
            }
        }

        return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/contacts/delete-nonereason", name="delete_nonereason_contacts")
     */
    public function deleteReasonAction($reportId)
    {
        //just do some checks to make sure user is allowed to update this report
        $report = $this->getReport($reportId, ['basic', 'transactions']);

        if (!empty($report)) {
            $report->setReasonForNoContacts(null);
            $this->get('restClient')->put('report/'.$report->getId(), $report);
        }

        return $this->redirect($this->generateUrl('contacts', ['reportId' => $report->getId()]));
    }

    /**
     * @Route("/report/{reportId}/contacts/nonereason", name="edit_contacts_nonereason")
     * @Template("AppBundle:Contact:edit_none_reason.html.twig")
     */
    public function noneReasonAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client']);

        $form = $this->createForm(new FormDir\ReasonForNoContactType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->get('restClient')->put('report/'.$reportId, $data);

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
     * @Template("AppBundle:Contact:_none_reason_form.html.twig")
     */
    public function _noneReasonFormAction(Request $request, $reportId)
    {
        $actionUrl = $this->generateUrl('edit_contacts_nonereason', ['reportId' => $reportId]);
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client']);
        $form = $this->createForm(new FormDir\ReasonForNoContactType(), $report, ['action' => $actionUrl]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->get('restClient')->put('report/'.$reportId, $data);
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }
}

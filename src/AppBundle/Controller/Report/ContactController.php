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
    public function startAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['contact']);

        if (count($report->getContacts()) > 0 || !empty($report->getReasonForNoContacts())) {
            return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/exist", name="contacts_exist")
     * @Template("AppBundle:Report/Contact:exist.html.twig")
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['contact']);
        $form = $this->createForm(new FormDir\Report\ContactExistType(), $report);
        $form->handleRequest($request);

        if (!$form->isSubmitted() && $report->getReasonForNoContacts()) {
            $form->get('exist')->setData('no');
        }

        if ($form->isValid()) {
            switch ($form['exist']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contact_add', ['reportId' => $reportId]);
                case 'no':
                    $this->get('restClient')->put('report/' . $reportId, $report, ['reasonForNoContacts', 'contacts']);
                    foreach($report->getContacts() as $contact) {
                        $this->getRestClient()->delete("/report/contact/".$contact->getId());
                    }
                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('contacts', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'summary') {
            $backLink = $this->generateUrl('contacts_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/add", name="contact_add")
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
                $this->getRestClient()->post('report/contact', $data, ['contact', 'report-id']);

                return $this->redirect($this->generateUrl('contact_add_another', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('contacts_exist', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'another') {
            $backLink = $this->generateUrl('contact_add_another', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/contacts/add_another", name="contact_add_another")
     * @Template("AppBundle:Report/Contact:add_another.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\Report\ContactAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contact_add', ['reportId' => $reportId, 'from'=>'another']);
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
     * @Route("/report/{reportId}/contacts/edit/{contactId}", name="contact_edit")
     * @Template("AppBundle:Report/Contact:add.html.twig")
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

            $this->getRestClient()->put('report/contact', $data);
            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));

        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/contacts/list", name="contacts_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['contact']);
        $contacts = $report->getContacts();

        return [
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/contacts/{id}/delete", name="contact_delete")
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
}

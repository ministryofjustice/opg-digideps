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
    private static $jmsGroups = [
        'contact',
        'contact-status',
    ];

    /**
     * @Route("/report/{reportId}/contacts", name="contacts")
     * @Template("AppBundle:Report/Contact:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getContactsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
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
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\ContactExistType::class, $report);
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
     * @Template("AppBundle:Report/Contact:add.html.twig")
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = new EntityDir\Report\Contact();

        $form = $this->createForm(FormDir\Report\ContactType::class, $contact);
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
     * @Template("AppBundle:Report/Contact:addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-contacts']);
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
     * @Template("AppBundle:Report/Contact:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $contactId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->getRestClient()->get('report/contact/' . $contactId, 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(FormDir\Report\ContactType::class, $contact);
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
     * @Template("AppBundle:Report/Contact:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getContactsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('contacts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/{contactId}/delete", name="contacts_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $contactId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->delete("/report/contact/{$contactId}");

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Contact deleted'
            );

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->getRestClient()->get('report/contact/' . $contactId, 'Report\\Contact');

        return [
            'translationDomain' => 'report-contacts',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.contactName', 'value' => $contact->getContactName()],
                ['label' => 'deletePage.summary.relationship', 'value' => $contact->getRelationship()],
                ['label' => 'deletePage.summary.explanation', 'value' => $contact->getExplanation()],
            ],
            'backLink' => $this->generateUrl('contacts', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'contacts';
    }
}

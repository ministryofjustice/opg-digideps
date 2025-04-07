<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ContactController extends AbstractController
{
    private static $jmsGroups = [
        'contact',
        'contact-status',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private ClientApi $clientApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/contacts", name="contacts")
     *
     * @Template("@App/Report/Contact/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getContactsState()['state']) {
            return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/exist", name="contacts_exist")
     *
     * @Template("@App/Report/Contact/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\ContactExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['hasContacts']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from' => 'exist']);
                case 'no':
                    $this->restClient->put('report/'.$reportId, $report, ['reasonForNoContacts', 'contacts']);
                    foreach ($report->getContacts() as $contact) {
                        $this->restClient->delete('/report/contact/'.$contact->getId());
                    }

                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('contacts', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('contacts_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/add", name="contacts_add")
     *
     * @Template("@App/Report/Contact/add.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = new EntityDir\Report\Contact();

        $form = $this->createForm(FormDir\Report\ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            // update contact. The API will also delete reason for no contact
            $this->restClient->post('report/contact', $data, ['contact', 'report-id']);

            return $this->redirect($this->generateUrl('contacts_add_another', ['reportId' => $reportId]));
        }

        try {
            $backLinkRoute = 'contacts_'.$request->get('from');
            $backLink = $this->generateUrl($backLinkRoute, ['reportId' => $reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report,
            ];
        } catch (RouteNotFoundException $e) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report,
            ];
        }
    }

    /**
     * @Route("/report/{reportId}/contacts/add_another", name="contacts_add_another")
     *
     * @Template("@App/Report/Contact/addAnother.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-contacts']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from' => 'add_another']);
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
     *
     * @Template("@App/Report/Contact/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, $reportId, int $contactId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->restClient->get('report/contact/'.$contactId, 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(FormDir\Report\ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $request->getSession()->getFlashBag()->add('notice', 'Contact edited');

            $this->restClient->put('report/contact', $data);

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('contacts_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/summary", name="contacts_summary")
     *
     * @Template("@App/Report/Contact/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getContactsState()['state']) {
            return $this->redirectToRoute('contacts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/contacts/{contactId}/delete", name="contacts_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, int $contactId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete("/report/contact/{$contactId}");

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Contact deleted'
            );

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->restClient->get('report/contact/'.$contactId, 'Report\\Contact');

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

<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\Contact;
use App\Entity\Report\Status;
use App\Form\AddAnotherRecordType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\ContactExistType;
use App\Form\Report\ContactType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ContactController extends AbstractController
{
    private static array $jmsGroups = [
        'contact',
        'contact-status',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
    ) {
    }

    #[Route(path: '/report/{reportId}/contacts', name: 'contacts')]
    #[Template('@App/Report/Contact/start.html.twig')]
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getContactsState()['state']) {
            return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/contacts/exist', name: 'contacts_exist')]
    #[Template('@App/Report/Contact/exist.html.twig')]
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(ContactExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['hasContacts']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from' => 'exist']);
                case 'no':
                    $this->restClient->put('report/' . $reportId, $report, ['reasonForNoContacts', 'contacts']);
                    foreach ($report->getContacts() as $contact) {
                        $this->restClient->delete('/report/contact/' . $contact->getId());
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

    #[Route(path: '/report/{reportId}/contacts/add', name: 'contacts_add')]
    #[Template('@App/Report/Contact/add.html.twig')]
    public function addAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            // update contact. The API will also delete reason for no contact
            $this->restClient->post('report/contact', $data, ['contact', 'report-id']);

            /** @var Form $addAnother */
            $addAnother = $form->get('addAnother');
            switch ($addAnother->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        try {
            $backLinkRoute = 'contacts_' . $request->get('from');
            $backLink = $this->generateUrl($backLinkRoute, ['reportId' => $reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report,
            ];
        } catch (RouteNotFoundException) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report,
            ];
        }
    }

    #[Route(path: '/report/{reportId}/contacts/edit/{contactId}', name: 'contacts_edit')]
    #[Template('@App/Report/Contact/edit.html.twig')]
    public function editAction(Request $request, int $reportId, int $contactId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->restClient->get("report/contact/$contactId", 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $request->getSession()->getFlashBag()->add('notice', 'Contact edited');

            $this->restClient->put('report/contact', $data);
            /** @var Form $addAnother */
            $addAnother = $form->get('addAnother');
            switch ($addAnother->getData()) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => $this->generateUrl('contacts_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/contacts/summary', name: 'contacts_summary')]
    #[Template('@App/Report/Contact/summary.html.twig')]
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED == $report->getStatus()->getContactsState()['state']) {
            return $this->redirectToRoute('contacts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/contacts/{contactId}/delete', name: 'contacts_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, int $contactId): array|RedirectResponse
    {
        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete("/report/contact/$contactId");

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Contact deleted'
            );

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $contact = $this->restClient->get("report/contact/$contactId", 'Report\\Contact');

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
}

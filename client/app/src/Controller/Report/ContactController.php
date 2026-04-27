<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller\Report;

use OPG\Digideps\Frontend\Controller\AbstractController;
use OPG\Digideps\Frontend\Entity\Report\Contact;
use OPG\Digideps\Frontend\Entity\Report\Status;
use OPG\Digideps\Frontend\Form\AddAnotherThingType;
use OPG\Digideps\Frontend\Form\ConfirmDeleteType;
use OPG\Digideps\Frontend\Form\Report\ContactExistType;
use OPG\Digideps\Frontend\Form\Report\ContactType;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Common\Validating\ValidatingForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
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

        $status = $report->getStatus()->getContactsState();
        if (Status::STATE_NOT_STARTED != $status['state']) {
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
            $validatingForm = new ValidatingForm($form);
            $hasContacts = $validatingForm->getStringOrNull('hasContacts');

            switch ($hasContacts) {
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
        if ('summary' == $request->query->getString('from', $request->getPayload()->getString('from'))) {
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
        $form->add('addAnother', AddAnotherThingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $data = $validatingForm->getObjectOrThrow(null, Contact::class);
            $data->setReport($report);

            // update contact. The API will also delete reason for no contact
            $this->restClient->post('report/contact', $data, ['contact', 'report-id']);

            $addAnother = $validatingForm->getStringOrNull('addAnother');
            switch ($addAnother) {
                case 'yes':
                    return $this->redirectToRoute('contacts_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
            }
        }

        try {
            $backLinkRoute = 'contacts_' . $request->query->getString('from', $request->getPayload()->getString('from'));
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

        /** @var Contact $contact */
        $contact = $this->restClient->get("report/contact/$contactId", 'Report\\Contact');
        $contact->setReport($report);

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $data = $validatingForm->getObjectOrThrow(null, Contact::class);
            $data->setReport($report);

            $this->restClient->put('report/contact', $data);

            if ($request->getSession() instanceof Session) {
                $request->getSession()->getFlashBag()->add('notice', 'Contact edited');
            }

            return $this->redirectToRoute('contacts_summary', ['reportId' => $reportId]);
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

        $status = $report->getStatus()->getContactsState();
        if (Status::STATE_NOT_STARTED == $status['state']) {
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

            if ($request->getSession() instanceof Session) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Contact deleted'
                );
            }

            return $this->redirect($this->generateUrl('contacts', ['reportId' => $reportId]));
        }

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        /** @var Contact $contact */
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

<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Time\DateTimeProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/org")
 */
class IndexController extends AbstractController
{
    /** @var Logger */
    private $logger;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    public function __construct(Logger $logger, DateTimeProvider $dateTimeProvider)
    {
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @Route("/", name="org_dashboard")
     * @Template("AppBundle:Org/Index:dashboard.html.twig")
     */
    public function dashboardAction(Request $request)
    {
        $currentFilters = [
            'q'                 => $request->get('q'),
            'status'            => $request->get('status'),
            'exclude_submitted' => true,
            'sort'              => 'end_date',
            'sort_direction'    => 'asc',
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
        ];

        /** @var User $user */
        $user = $this->getUser();

        $endpoint = sprintf(
            '%s?%s',
            $user->belongsToActiveOrganisation() ?'/report/get-all-by-orgs' : 'report/get-all-by-user',
            http_build_query($currentFilters)
        );

        $response = $this->getRestClient()->get($endpoint, 'array');

        $reports = $this->getRestClient()->arrayToEntities(EntityDir\Report\Report::class . '[]', $response['reports']);

        return [
            'filters' => $currentFilters,
            'reports' => $reports,
            'counts'  => [
                'total' => $response['counts']['total'],
                'notStarted' => $response['counts']['notStarted'],
                'notFinished' => $response['counts']['notFinished'],
                'readyToSubmit' => $response['counts']['readyToSubmit'],
            ],
        ];
    }

    /**
     * Client edit page
     * Report is only associated to one client, and it's needed for back link routing,
     * so it's retrieved with the report with a single API call
     *
     * @Route("/client/{clientId}/edit", name="org_client_edit")
     * @Template("AppBundle:Org/Index:clientEdit.html.twig")
     */
    public function clientEditAction(Request $request, $clientId)
    {
        try {
            /** @var Client $client */
            $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);
        } catch (RestClientException $e) {
            throw $this->createNotFoundException();
        }

        // PA client profile is ATM relying on report ID, this is a working until next refactor
        $returnLink = ($request->get('from') === 'declaration') ?
            $this->generateUrl('report_declaration', ['reportId' => $client->getCurrentReport()->getId()]) :
            $this->generateUrl('report_overview', ['reportId'=>$client->getCurrentReport()->getId()]);

        $oldEmail = $client->getEmail();

        $form = $this->createForm(FormDir\Org\ClientType::class, $client);
        $form->handleRequest($request);

        $newEmail = $client->getEmail();

        // edit client form
        if ($form->isSubmitted() && $form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, ['pa-edit']);

            if ($oldEmail !== $newEmail) {
                $event = (new AuditEvents($this->dateTimeProvider))->clientEmailChanged(
                    AuditEvents::TRIGGER_DEPUTY_USER_EDIT,
                    $oldEmail,
                    $newEmail,
                    $this->getUser()->getEmail(),
                    $clientUpdated->getFullName()
                );

                $this->logger->notice('', $event);

            }

            $this->addFlash('notice', 'The client details have been edited');

            return $this->redirect($returnLink);
        }

        return [
            'backLink' => $returnLink,
            'form' => $form->createView(),
            'client'=>$client,
        ];
    }

    /**
     * Client archive page
     *
     * @Route("/client/{clientId}/archive", name="org_client_archive")
     * @Template("AppBundle:Org/Index:clientArchive.html.twig")
     */
    public function clientArchiveAction(Request $request, $clientId)
    {
        /** @var Client $client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);

        // PA client profile is ATM relying on report ID, this is a working until next refactor
        $returnLink = $this->generateUrl('report_overview', ['reportId'=>$client->getCurrentReport()->getId()]);
        $form = $this->createForm(FormDir\Org\ClientArchiveType::class, $client);
        $form->handleRequest($request);

        /** @var SubmitButton $submitBtn */
        $submitBtn = $form->get('save');
        if ($submitBtn->isClicked() && $form->isSubmitted() && $form->isValid()) {
            if (true === $form->get('confirmArchive')->getData()) {
                $this->getRestClient()->apiCall('put', 'client/' . $client->getId() . '/archive', null, 'array');
                $this->addFlash('notice', 'The client has been archived');
                return $this->redirectToRoute('org_dashboard');
            } else {
                /** @var TranslatorInterface $translator */
                $translator = $this->get('translator');

                $form->get('confirmArchive')->addError(new FormError($translator->trans('form.error.confirmArchive', [], 'pa-client-archive')));
            }
        }

        return [
            'backLink' => $returnLink,
            'form' => $form->createView(),
            'client'=>$client,
        ];
    }
}

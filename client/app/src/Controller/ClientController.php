<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Event\RegistrationFailedEvent;
use App\Event\RegistrationSucceededEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\RestClientException;
use App\Form\ClientType;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\DeputyApi;
use App\Service\Client\Internal\PreRegistrationApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Redirector;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientController extends AbstractController
{
    public function __construct(
        private UserApi $userApi,
        private ClientApi $clientApi,
        private DeputyApi $deputyApi,
        private RestClient $restClient,
        private PreRegistrationApi $preRegistrationApi,
        private ObservableEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @Route("/deputyship-details/your-client", name="client_show_deprecated")
     *
     * @Template("@App/Client/show.html.twig")
     */
    public function showAction(Redirector $redirector): array|RedirectResponse
    {
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/deputyship-details/client/{clientId}", name="client_show")
     *
     * @Template("@App/Client/show.html.twig")
     */
    public function showClientDetailsAction(Redirector $redirector, int $clientId): array|RedirectResponse
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->userApi->getUserWithData();

        $route = $redirector->getCorrectRouteIfDifferent($user, 'client_show');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $client = $this->clientApi->getById($clientId);

        return [
            'client' => $client,
        ];
    }

    /**
     * @Route("/deputyship-details/your-client/edit", name="client_edit_deprecated")
     *
     * @Template("@App/Client/edit.html.twig")
     */
    public function editAction(Request $request): RedirectResponse
    {
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/deputyship-details/client/{clientId}/edit", name="client_edit")
     *
     * @Template("@App/Client/edit.html.twig")
     */
    public function editClientDetailsAction(Request $request, int $clientId): array|RedirectResponse
    {
        $from = $request->get('from');
        /** @var Client|null $preUpdateClient */
        $preUpdateClient = $this->clientApi->getById($clientId);

        if (is_null($preUpdateClient)) {
            /** @var User $user */
            $user = $this->getUser();
            $userId = $user->getId();
            throw new \RuntimeException("User $userId does not have a client");
        }

        $form = $this->createForm(ClientType::class, clone $preUpdateClient, [
            'action' => $this->generateUrl('client_edit', ['clientId' => $clientId, 'action' => 'edit', 'from' => $from]),
            'validation_groups' => ['lay-deputy-client-edit'],
            'include_court_date_field' => false,
        ]);

        $form->handleRequest($request);

        // edit client form
        if ($form->isSubmitted() && $form->isValid()) {
            $postUpdateClient = $form->getData();
            $postUpdateClient->setId($preUpdateClient->getId());
            $this->clientApi->update($preUpdateClient, $postUpdateClient, AuditEvents::TRIGGER_DEPUTY_USER_EDIT_SELF);

            $this->addFlash('clientEditSuccess', htmlentities($postUpdateClient->getFirstname())."'s details have been saved");

            $activeReport = $postUpdateClient->getActiveReport();

            if ('declaration' === $from && $activeReport instanceof Report) {
                return $this->redirect($this->generateUrl('report_confirm_details', ['reportId' => $activeReport->getId()]));
            }

            return $this->redirect($this->generateUrl('deputyship_details_clients'));
        }

        return [
            'client' => $preUpdateClient,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/client/add", name="client_add")
     *
     * @Template("@App/Client/add.html.twig")
     */
    public function addAction(
        Request $request,
        Redirector $redirector,
        TranslatorInterface $translator,
        LoggerInterface $logger,
    ): array|RedirectResponse {
        // redirect if user has missing details or is on wrong page
        $user = $this->userApi->getUserWithData();

        $route = $redirector->getCorrectRouteIfDifferent($user, 'client_add');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }
        /** @var Client|null $client */
        $client = $this->clientApi->getFirstClient();
        $existingClientId = 0;
        if (!empty($client)) {
            // update existing client
            $existingClientId = $client->getId();
            /** @var Client $client */
            $client = $this->restClient->get('client/'.$client->getId(), 'Client', ['client', 'report-id', 'current-report']);
            $client_validated = true;
        } else {
            // new client
            /** @var Client $client */
            $client = new Client();
            $client_validated = false;
        }

        $form = $this->createForm(ClientType::class, $client);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // validate against pre-registration data
                if (!$client_validated) {
                    $this->preRegistrationApi->verify($client);
                    $response = $this->clientApi->create($form->getData());
                } else {
                    $upsertData = $form->getData();
                    $upsertData->setId($existingClientId);
                    $response = $this->clientApi->update($client, $upsertData, AuditEvents::TRIGGER_DEPUTY_USER_EDIT_CLIENT_DURING_REGISTRATION);
                }

                $client->setId($response['id']);

                $report = new Report();
                $report->setClient($client);
                $this->restClient->post('report', $report);

                /** @var User $currentUser */
                $currentUser = $this->userApi->getUserWithData();

                $deputyResponse = $this->deputyApi->createDeputyFromUser($currentUser);
                $this->clientApi->updateDeputy($response['id'], $deputyResponse['id']);

                $event = new RegistrationSucceededEvent($currentUser);
                $this->eventDispatcher->dispatch($event, RegistrationSucceededEvent::DEPUTY);

                $url = $this->generateUrl('lay_home', ['clientId' => $response['id']]);

                return $this->redirect($url);
            } catch (\Throwable $e) {
                if (!$e instanceof RestClientException) {
                    if (method_exists($e, 'getData')) {
                        $failureData = json_decode($e->getData()['message'], true);

                        // If response from API is not valid json just log the message
                        $failureData = !is_array($failureData) ? ['failure_message' => $failureData] : $failureData;

                        $event = new RegistrationFailedEvent($failureData, $e->getMessage());
                        $this->eventDispatcher->dispatch($event, RegistrationFailedEvent::NAME);
                    }

                    throw $e;
                }

                switch ((int) $e->getCode()) {
                    case 400:
                        $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register')));
                        break;

                    case 403:
                        $form->addError(new FormError($translator->trans('formErrors.coDepCaseAlreadyRegistered', [], 'register')));
                        break;

                    case 425:
                        $form->addError(new FormError($translator->trans('formErrors.caseNumberAlreadyUsed', [], 'register')));
                        break;

                    case 460:
                        $form->addError(new FormError($translator->trans('matchingErrors.caseNumber', [], 'register')));
                        break;

                    case 461:
                        $decodedError = json_decode($e->getData()['message'], true);

                        if ($decodedError['matching_errors']['client_lastname']) {
                            $form->addError(new FormError($translator->trans('matchingErrors.clientLastname', [], 'register')));
                        }
                        if ($decodedError['matching_errors']['deputy_lastname']) {
                            $form->addError(new FormError($translator->trans('matchingErrors.deputyLastname', [], 'register')));
                        }
                        if ($decodedError['matching_errors']['deputy_firstname']) {
                            $form->addError(new FormError($translator->trans('matchingErrors.deputyFirstname', [], 'register')));
                        }
                        if ($decodedError['matching_errors']['deputy_postcode']) {
                            $form->addError(new FormError($translator->trans('matchingErrors.deputyPostcode', [], 'register')));
                        }
                        if ($decodedError['matching_errors']['deputy_lastname']
                            || $decodedError['matching_errors']['deputy_firstname']
                            || $decodedError['matching_errors']['deputy_postcode']
                        ) {
                            $form->addError(new FormError('Please click back'));
                        }

                        break;

                    case 462:
                        $form->addError(new FormError($translator->trans('formErrors.deputyNotUniquelyIdentified', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                }

                $logger->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'client_validated' => $client_validated,
            'client' => $client,
            'backLink' => $this->generateUrl('user_details'),
        ];
    }
}

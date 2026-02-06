<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\CoDeputyInviteType;
use App\Form\CoDeputyVerificationType;
use App\Model\SelfRegisterData;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\DeputyApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Redirector;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CoDeputyController extends AbstractController
{
    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly UserApi $userApi,
        private readonly DeputyApi $deputyApi,
        private readonly RestClient $restClient,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * For co-deputies to verify their details and complete registration.
     */
    #[Route(path: '/codeputy/verification', name: 'codep_verification')]
    #[Template('@App/CoDeputy/verification.html.twig')]
    public function verificationAction(Request $request, Redirector $redirector, ValidatorInterface $validator): array|RedirectResponse
    {
        $user = $this->userApi->getUserWithData(['user', 'user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $redirector->getCorrectRouteIfDifferent($user, 'codep_verification')) {
            return $this->redirectToRoute($route);
        }

        $form = $this->createForm(CoDeputyVerificationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // get client validation errors, if any, and add to the form
            $client = new Client();

            /** @var string $lastName */
            $lastName = $form['clientLastname']->getData();
            $client->setLastName($lastName);

            /** @var string $caseNumber */
            $caseNumber = $form['clientCaseNumber']->getData();
            $client->setCaseNumber($caseNumber);

            $errors = $validator->validate($client, null, ['verify-codeputy']);

            foreach ($errors as $error) {
                $clientProperty = $error->getPropertyPath();
                $form->get('client' . ucfirst($clientProperty))->addError(new FormError("{$error->getMessage()}"));
            }

            if ($form->isValid()) {
                $selfRegisterData = new SelfRegisterData();
                $selfRegisterData->setFirstname($form['firstname']->getData());
                $selfRegisterData->setLastname($form['lastname']->getData());
                $selfRegisterData->setEmail($form['email']->getData());
                $selfRegisterData->setPostcode($form['addressPostcode']->getData());
                $selfRegisterData->setClientLastname($form['clientLastname']->getData());

                // truncate case number if length is 10 digits long before setting
                /** @var string $caseNumber */
                $caseNumber = $form['clientCaseNumber']->getData();
                if (10 == strlen($caseNumber)) {
                    $selfRegisterData->setCaseNumber(substr($caseNumber, 0, -2));
                } else {
                    $selfRegisterData->setCaseNumber($caseNumber);
                }

                $clientId = $this->restClient->get('v2/client/case-number/' . $selfRegisterData->getCaseNumber(), 'Client')->getId();

                /** @var Client $mainClient */
                $mainClient = $this->restClient->get('client/' . $clientId, 'Client', ['client', 'client-users', 'report-id', 'current-report', 'user']);
                $deputies = $mainClient->getUsers();
                $mainDeputy = reset($deputies);

                // validate against pre-registration data
                try {
                    $coDeputyVerificationData = $this->restClient->apiCall('post', 'selfregister/verifycodeputy', $selfRegisterData, 'array', [], false);

                    if ($mainDeputy !== false && $mainDeputy->isNdrEnabled()) {
                        $user->setNdrEnabled(true);
                    }

                    $this->restClient->put('user/' . $user->getId(), $user);

                    /** @var User $user */
                    $user = $this->restClient->apiCall('put', 'selfregister/updatecodeputy/' . $user->getId(), $coDeputyVerificationData, 'User', [], false);

                    $this->deputyApi->createDeputyFromUser($user);

                    return $this->redirect($this->generateUrl('homepage'));
                } catch (\Throwable $e) {
                    $translator = $this->translator;

                    match ((int) $e->getCode()) {
                        422 => $form->addError(new FormError(
                            $translator->trans('email.first.existingError', [
                                '%login%' => $this->generateUrl('login'),
                                '%passwordForgotten%' => $this->generateUrl('password_forgotten'),
                            ], 'register')
                        )),
                        421 => $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register'))),
                        424 => $form->get('addressPostcode')->addError(new FormError($translator->trans('postcode.matchingError', [], 'register'))),
                        425 => $form->addError(new FormError($translator->trans('formErrors.caseNumberAlreadyUsed', [], 'register'))),
                        462 => $form->addError(new FormError($translator->trans('formErrors.deputyNotUniquelyIdentified', [], 'register'))),
                        463 => $form->addError(new FormError($translator->trans('formErrors.deputyAlreadyLinkedToCaseNumber', [], 'register'))),
                        default => $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register'))),
                    };

                    $this->logger->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
                }
            }
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
            'client_validated' => false,
        ];
    }

    /**
     * For an existing deputy to invite a co-deputy (who must exist in the pre_registration table).
     *
     * @throws \Throwable
     */
    #[Route(path: '/codeputy/{clientId}/add', name: 'add_co_deputy')]
    #[Template('@App/CoDeputy/add.html.twig')]
    public function addAction(Request $request, Redirector $redirector, int $clientId): array|RedirectResponse
    {
        $loggedInUser = $this->userApi->getUserWithData(['user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $redirector->getCorrectRouteIfDifferent($loggedInUser, 'add_co_deputy')) {
            return $this->redirectToRoute($route);
        }

        $invitedUser = new User();
        $form = $this->createForm(CoDeputyInviteType::class, $invitedUser);

        $backLink = $this->generateUrl('courtorders_for_deputy');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userApi->createCoDeputy($invitedUser, $loggedInUser, $clientId);

                $this->userApi->update(
                    $loggedInUser,
                    $loggedInUser->setCoDeputyClientConfirmed(true),
                    AuditEvents::TRIGGER_CODEPUTY_CREATED
                );

                $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation has been sent');

                return $this->redirect($backLink);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->translator->trans('form.email.existingError', [], 'co-deputy')));
                        break;
                    default:
                        $this->logger->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
                        throw $e;
                }
                $this->logger->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'backLink' => $backLink,
            'client' => $this->clientApi->getFirstClient(),
        ];
    }

    /**
     * For an existing deputy to resend an invite to a co-deputy.
     *
     * @throws \Throwable
     */
    #[Route(path: '/codeputy/re-invite/{email}', name: 'codep_resend_activation')]
    #[Template('@App/CoDeputy/resendActivation.html.twig')]
    public function resendActivationAction(Request $request, string $email): array|RedirectResponse
    {
        $loggedInUser = $this->userApi->getUserWithData(['user-clients', 'client']);
        $existingCoDeputy = $this->userApi->getByEmail($email);

        $form = $this->createForm(CoDeputyInviteType::class, $existingCoDeputy);

        $backLink = $this->generateUrl('courtorders_for_deputy');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $firstName = $existingCoDeputy->getFirstName();
                $lastName = $existingCoDeputy->getLastName();

                $formEmail = $form->getData()->getEmail();
                $formFirstName = $form->getData()->getFirstName();
                $formLastName = $form->getData()->getLastName();

                // firstname, lastname or email were updated on the fly
                if ($formEmail != $email || $formFirstName != $firstName || $formLastName != $lastName) {
                    $this->restClient->put('codeputy/' . $existingCoDeputy->getId(), $form->getData(), []);
                }

                $this->userApi->reInviteCoDeputy($formEmail, $loggedInUser);

                $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation was re-sent');

                return $this->redirect($backLink);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->translator->trans('form.email.existingError', [], 'co-deputy')));
                        break;
                    default:
                        $this->logger->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
                        throw $e;
                }
                $this->logger->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'backLink' => $backLink,
        ];
    }
}

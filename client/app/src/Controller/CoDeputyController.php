<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\CoDeputyInviteType;
use App\Form\CoDeputyVerificationType;
use App\Service\Client\Internal\DeputyApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Redirector;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CoDeputyController extends AbstractController
{
    public function __construct(
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
            return $this->redirect($route);
        }

        $form = $this->createForm(CoDeputyVerificationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // get client validation errors, if any, and add to the form
            $client = new Client();

            if ($form->has('clientLastname') && $form->get('clientLastname') !== null) {
                /** @var string $lastName */
                $lastName = $form->get('clientLastname')->getData();
                $client->setLastName($lastName);
            }

            if ($form->has('clientCaseNumber') && $form->get('clientCaseNumber') !== null) {
                /** @var string $caseNumber */
                $caseNumber = $form->get('clientCaseNumber')->getData();
                $client->setCaseNumber($caseNumber);
            }

            $errors = $validator->validate($client, null, ['verify-codeputy']);

            foreach ($errors as $error) {
                $clientProperty = $error->getPropertyPath();
                $form->get('client' . ucfirst($clientProperty))->addError(new FormError("{$error->getMessage()}"));
            }

            if ($form->isValid()) {
                $selfRegisterData = new SelfRegisterData();
                if ($form->has('firstname') && $form->get('firstname') !== null) {
                    /** @var string $firstName */
                    $firstName = $form->get('firstname')->getData();
                    $selfRegisterData->setFirstname($firstName);
                }
                if ($form->has('lastname') && $form->get('lastname') !== null) {
                    /** @var string $lastName */
                    $lastName = $form->get('lastname')->getData();
                    $selfRegisterData->setLastname($lastName);
                }
                if ($form->has('email') && $form->get('email') !== null) {
                    /** @var string $email */
                    $email = $form->get('email')->getData();
                    $selfRegisterData->setEmail($email);
                }
                if ($form->has('addressPostcode') && $form->get('addressPostcode') !== null) {
                    /** @var string $postcode */
                    $postcode = $form->get('addressPostcode')->getData();
                    $selfRegisterData->setPostcode($postcode);
                }
                if ($form->has('clientLastname') && $form->get('clientLastname') !== null) {
                    /** @var string $clientLastName */
                    $clientLastName = $form->get('clientLastname')->getData();
                    $selfRegisterData->setClientLastname($clientLastName);
                }

                if ($form->has('clientCaseNumber') && $form->get('clientCaseNumber') !== null) {
                    // truncate case number if length is 10 digits long before setting
                    /** @var string $caseNumber */
                    $caseNumber = $form->get('clientCaseNumber')->getData();
                    if (10 == strlen($caseNumber)) {
                        $selfRegisterData->setCaseNumber(substr($caseNumber, 0, -2));
                    } else {
                        $selfRegisterData->setCaseNumber($caseNumber);
                    }
                }

                /** @var Client $client */
                $client = $this->restClient->get('v2/client/case-number/' . $selfRegisterData->getCaseNumber(), 'Client');
                $clientId = $client->getId();

                /** @var Client $mainClient */
                $mainClient = $this->restClient->get('client/' . $clientId, 'Client', ['client', 'client-users', 'report-id', 'current-report', 'user']);
                $deputies = $mainClient->getUsers();
                $mainDeputy = reset($deputies);

                // validate against pre-registration data
                try {
                    $coDeputyVerificationData = $this->restClient->apiCall('post', 'selfregister/verifycodeputy', $selfRegisterData, 'array', [], false);

                    $this->restClient->put('user/' . $user->getId(), $user);

                    /** @var User $user */
                    $user = $this->restClient->apiCall('put', 'selfregister/updatecodeputy/' . $user->getId(), $coDeputyVerificationData, 'User', [], false);

                    $this->deputyApi->createDeputyFromUser($user);

                    // Update codeputy flag to true for main deputy user account
                    if ($mainDeputy !== false) {
                        $this->userApi->updateUserCodeputyFlagToTrue($mainDeputy->getId());
                    }

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

                /** @var User $coDeputy */
                $coDeputy = $form->getData();
                $formEmail = $coDeputy->getEmail();
                $formFirstName = $coDeputy->getFirstName();
                $formLastName = $coDeputy->getLastName();

                // firstname, lastname or email were updated on the fly
                if ($formEmail != $email || $formFirstName != $firstName || $formLastName != $lastName) {
                    $this->restClient->put('codeputy/' . $existingCoDeputy->getId(), $coDeputy, []);
                }

                $this->userApi->reInviteCoDeputy($formEmail, $loggedInUser);

                if ($request->getSession() instanceof Session) {
                    $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation was re-sent');
                }

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

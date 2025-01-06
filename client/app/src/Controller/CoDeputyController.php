<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Entity\User;
use App\Form as FormDir;
use App\Model\SelfRegisterData;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\DeputyApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Redirector;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CoDeputyController extends AbstractController
{
    private ClientApi $clientApi;
    private UserApi $userApi;
    private DeputyApi $deputyApi;
    private RestClient $restClient;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;

    public function __construct(
        ClientApi $clientApi,
        UserApi $userApi,
        DeputyApi $deputyApi,
        RestClient $restClient,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->clientApi = $clientApi;
        $this->userApi = $userApi;
        $this->deputyApi = $deputyApi;
        $this->restClient = $restClient;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @Route("/codeputy/verification", name="codep_verification")
     *
     * @Template("@App/CoDeputy/verification.html.twig")
     */
    public function verificationAction(Request $request, Redirector $redirector, ValidatorInterface $validator)
    {
        $user = $this->userApi->getUserWithData(['user', 'user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $redirector->getCorrectRouteIfDifferent($user, 'codep_verification')) {
            return $this->redirectToRoute($route);
        }

        $form = $this->createForm(FormDir\CoDeputyVerificationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // get client validation errors, if any, and add to the form
            $client = new EntityDir\Client();
            $client->setLastName($form['clientLastname']->getData());
            $client->setCaseNumber($form['clientCaseNumber']->getData());

            $errors = $validator->validate($client, null, ['verify-codeputy']);

            foreach ($errors as $error) {
                $clientProperty = $error->getPropertyPath();
                $form->get('client'.ucfirst($clientProperty))->addError(new FormError($error->getMessage()));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $selfRegisterData = new SelfRegisterData();
                $selfRegisterData->setFirstname($form['firstname']->getData());
                $selfRegisterData->setLastname($form['lastname']->getData());
                $selfRegisterData->setEmail($form['email']->getData());
                $selfRegisterData->setPostcode($form['addressPostcode']->getData());
                $selfRegisterData->setClientLastname($form['clientLastname']->getData());
                $selfRegisterData->setCaseNumber($form['clientCaseNumber']->getData());

                $clientId = $this->restClient->get('v2/client/case-number/'.$selfRegisterData->getCaseNumber(), 'Client')->getId();
                $mainClient = $this->restClient->get('client/'.$clientId, 'Client', ['client', 'client-users', 'report-id', 'current-report', 'user']);
                $mainDeputy = reset($mainClient->getUsers());

                // validate against pre-registration data
                try {
                    $coDeputyVerificationData = $this->restClient->apiCall('post', 'selfregister/verifycodeputy', $selfRegisterData, 'array', [], false);

                    if ($mainDeputy->isNdrEnabled()) {
                        $user->setNdrEnabled(true);
                    }

                    $this->restClient->put('user/'.$user->getId(), $user);

                    /** @var User $user */
                    $user = $this->restClient->apiCall('put', 'selfregister/updatecodeputy/'.$user->getId(), $coDeputyVerificationData, 'User', [], false);

                    $this->deputyApi->createDeputyFromUser($user);

                    return $this->redirect($this->generateUrl('homepage'));
                } catch (\Throwable $e) {
                    $translator = $this->translator;

                    switch ((int) $e->getCode()) {
                        case 422:
                            $form->addError(new FormError(
                                $translator->trans('email.first.existingError', [
                                    '%login%' => $this->generateUrl('login'),
                                    '%passwordForgotten%' => $this->generateUrl('password_forgotten'),
                                ], 'register')
                            ));
                            break;

                        case 421:
                            $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register')));
                            break;

                        case 424:
                            $form->get('addressPostcode')->addError(new FormError($translator->trans('postcode.matchingError', [], 'register')));
                            break;

                        case 425:
                            $form->addError(new FormError($translator->trans('formErrors.caseNumberAlreadyUsed', [], 'register')));
                            break;

                        case 462:
                            $form->addError(new FormError($translator->trans('formErrors.deputyNotUniquelyIdentified', [], 'register')));
                            break;

                        case 463:
                            $form->addError(new FormError($translator->trans('formErrors.deputyAlreadyLinkedToCaseNumber', [], 'register')));
                            break;

                        default:
                            $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                    }

                    $this->logger->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
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
     * @Route("/codeputy/{clientId}/add", name="add_co_deputy")
     *
     * @Template("@App/CoDeputy/add.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws \Throwable
     */
    public function addAction(Request $request, Redirector $redirector, $clientId)
    {
        $loggedInUser = $this->userApi->getUserWithData(['user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $redirector->getCorrectRouteIfDifferent($loggedInUser, 'add_co_deputy')) {
            return $this->redirectToRoute($route);
        }

        $invitedUser = new User();
        $form = $this->createForm(FormDir\CoDeputyInviteType::class, $invitedUser);

        $backLink = $this->generateUrl('lay_home', ['clientId' => $loggedInUser->getFirstClient()->getId()]);

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
                        $this->logger->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
                        throw $e;
                }
                $this->logger->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'backLink' => $backLink,
            'client' => $this->clientApi->getFirstClient(),
        ];
    }

    /**
     * @Route("/codeputy/re-invite/{email}", name="codep_resend_activation")
     *
     * @Template("@App/CoDeputy/resendActivation.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws \Throwable
     */
    public function resendActivationAction(Request $request, string $email)
    {
        $loggedInUser = $this->userApi->getUserWithData(['user-clients', 'client']);
        $existingCoDeputy = $this->userApi->getByEmail($email);

        $form = $this->createForm(FormDir\CoDeputyInviteType::class, $existingCoDeputy);

        $backLink = $this->generateUrl('lay_home', ['clientId' => $loggedInUser->getFirstClient()->getId()]);

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
                    $this->restClient->put('codeputy/'.$existingCoDeputy->getId(), $form->getData(), []);
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
                        $this->logger->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
                        throw $e;
                }
                $this->logger->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'backLink' => $backLink,
        ];
    }
}

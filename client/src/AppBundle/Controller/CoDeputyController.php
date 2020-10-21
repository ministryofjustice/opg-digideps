<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Service\Client\Internal\ClientApi;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Redirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class CoDeputyController extends AbstractController
{
    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var UserApi
     */
    private $userApi;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @var MailSender
     */
    private $mailSender;

    public function __construct(
        ClientApi $clientApi,
        UserApi $userApi,
        RestClient $restClient,
        MailFactory $mailFactory,
        MailSender $mailSender
    )
    {
        $this->clientApi = $clientApi;
        $this->userApi = $userApi;
        $this->restClient = $restClient;
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
    }

    /**
     * @Route("/codeputy/verification", name="codep_verification")
     * @Template("AppBundle:CoDeputy:verification.html.twig")
     */
    public function verificationAction(Request $request, Redirector $redirector)
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
            $errors = $this->get('validator')->validate($client, null, ['verify-codeputy']);
            foreach ($errors as $error) {
                $clientProperty = $error->getPropertyPath();
                $form->get('client' . ucfirst($clientProperty))->addError(new FormError($error->getMessage()));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $selfRegisterData = new SelfRegisterData();
                $selfRegisterData->setFirstname($form['firstname']->getData());
                $selfRegisterData->setLastname($form['lastname']->getData());
                $selfRegisterData->setEmail($form['email']->getData());
                $selfRegisterData->setPostcode($form['addressPostcode']->getData());
                $selfRegisterData->setClientLastname($form['clientLastname']->getData());
                $selfRegisterData->setCaseNumber($form['clientCaseNumber']->getData());

                // validate against casRec
                try {
                    $this->restClient->apiCall('post', 'selfregister/verifycodeputy', $selfRegisterData, 'array', [], false);
                    $user->setCoDeputyClientConfirmed(true);
                    $this->restClient->put('user/' . $user->getId(), $user);
                    return $this->redirect($this->generateUrl('homepage'));
                } catch (\Throwable $e) {
                    $translator = $this->get('translator');
                    switch ((int) $e->getCode()) {
                        case 422:
                            $form->addError(new FormError(
                                $translator->trans('email.first.existingError', [
                                    '%login%' => $this->generateUrl('login'),
                                    '%passwordForgotten%' => $this->generateUrl('password_forgotten')
                                ], 'register')));
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

                        default:
                            $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                    }

                    $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
                }
            }
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
            'client_validated' => false
        ];
    }

    /**
     * @Route("/codeputy/{clientId}/add", name="add_co_deputy")
     * @Template("AppBundle:CoDeputy:add.html.twig")
     *
     * @param Request $request
     * @param Redirector $redirector
     *
     * @return array|RedirectResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, Redirector $redirector)
    {
        $loggedInUser = $this->userApi->getUserWithData(['user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $redirector->getCorrectRouteIfDifferent($loggedInUser, 'add_co_deputy')) {
            return $this->redirectToRoute($route);
        }

        $invitedUser = new EntityDir\User();

        $form = $this->createForm(FormDir\CoDeputyInviteType::class, $invitedUser);

        $backLink = $loggedInUser->isNdrEnabled() ?
            $this->generateUrl('ndr_index')
            :$this->generateUrl('lay_home');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var EntityDir\User $invitedUser */
                $invitedUser = $this->restClient->post('codeputy/add', $form->getData(), ['codeputy'], 'User');

                // Regular deputies should become coDeputies via a CSV import, but at least for testing handle the change from non co-dep to co-dep here
                $this->restClient->put('user/' . $loggedInUser->getId(), ['co_deputy_client_confirmed' => true], []);

                $invitationEmail = $this->mailFactory->createInvitationEmail($invitedUser, $loggedInUser->getFullName());
                $this->mailSender->send($invitationEmail);

                $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation has been sent');

                return $this->redirect($backLink);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'co-deputy')));
                        break;
                    default:
                        $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
                        throw $e;
                }
                $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'backLink' => $backLink,
            'client' => $this->clientApi->getFirstClient()
        ];
    }

    /**
     * @Route("/codeputy/re-invite/{email}", name="codep_resend_activation")
     * @Template("AppBundle:CoDeputy:resendActivation.html.twig")
     *
     * @param Request $request
     * @param $email
     *
     * @return array|RedirectResponse
     * @throws \Throwable
     */
    public function resendActivationAction(Request $request, $email)
    {
        $loggedInUser = $this->userApi->getUserWithData(['user-clients', 'client']);
        $invitedUser = $this->restClient->userRecreateToken($email, 'pass-reset');

        $form = $this->createForm(FormDir\CoDeputyInviteType::class, $invitedUser);

        $backLink = $loggedInUser->isNdrEnabled() ?
            $this->generateUrl('ndr_index')
            :$this->generateUrl('lay_home');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                //email was updated on the fly
                if ($form->getData()->getEmail() != $email) {
                    $this->restClient->put('codeputy/' . $invitedUser->getId(), $form->getData(), []);
                }
                $invitationEmail = $this->mailFactory->createInvitationEmail($invitedUser, $loggedInUser->getFullName());
                $this->mailSender->send($invitationEmail);
                $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation was re-sent');

                return $this->redirect($backLink);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'co-deputy')));
                        break;
                    default:
                        $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
                        throw $e;
                }
                $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'backLink' => $backLink
        ];
    }
}

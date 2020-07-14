<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Redirector;
use AppBundle\Service\Time\DateTimeProvider;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsController extends AbstractController
{
    /** @var MailFactory */
    private $mailFactory;

    /** @var MailSender */
    private $mailSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    public function __construct(
        MailFactory $mailFactory,
        MailSender $mailSender,
        TranslatorInterface $translator,
        Logger $logger,
        DateTimeProvider $dateTimeProvider
    ) {
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * @Route("/deputyship-details", name="account_settings")
     * @Route("/org/settings", name="org_settings")
     * @Template("AppBundle:Settings:index.html.twig")
     **/
    public function indexAction(Redirector $redirector)
    {
        if ($this->getUser()->isDeputyOrg()) {
            $user = $this->getUserWithData(['user-organisations', 'organisation']);

            return [
                'hasOrganisations' => count($user->getOrganisations()),
            ];
        };

        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData(['user-clients', 'client', 'client-reports', 'report']);
        if ($route = $redirector->getCorrectRouteIfDifferent($user, 'account_settings')) {
            return $this->redirectToRoute($route);
        }

        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;
        return [
            'client' => $client,
        ];
    }

    /**
     * @Route("/deputyship-details/your-details/change-password", name="user_password_edit")
     * @Route("/org/settings/your-details/change-password", name="org_profile_password_edit")
     * @Template("AppBundle:Settings:passwordEdit.html.twig")
     */
    public function passwordEditAction(Request $request)
    {
        $user = $this->getUserWithData();

        $form = $this->createForm(FormDir\ChangePasswordType::class, $user, [
            'mapped' => false,
            'error_bubbling' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $request->request->get('change_password')['plain_password']['first'];
            $this->getRestClient()->put('user/' . $user->getId() . '/set-password', json_encode([
                'password_plain' => $plainPassword,
            ]));
            $request->getSession()->set('login-context', 'password-update');

            $successRoute = $this->getUser()->isDeputyOrg() ? 'org_settings' : 'account_settings';
            return $this->redirect($this->generateUrl($successRoute));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * - display the Your details page
     *
     * @Route("/deputyship-details/your-details", name="user_show")
     * @Route("/org/settings/your-details", name="org_profile_show")
     * @Template("AppBundle:Settings:profile.html.twig")
     **/
    public function profileAction()
    {
        return [
            'user' => $this->getUser()
        ];
    }

    /**
     * Change your own detials
     *
     * @Route("/deputyship-details/your-details/edit", name="user_edit")
     * @Route("/org/settings/your-details/edit", name="org_profile_edit")
     * @Template("AppBundle:Settings:profileEdit.html.twig")
     * @throw AccessDeniedException
     **/
    public function profileEditAction(Request $request)
    {
        $user = $this->getUserWithData();

        if ($this->isGranted(EntityDir\User::ROLE_ADMIN) || $this->isGranted(EntityDir\User::ROLE_AD)) {
            $form = $this->createForm(FormDir\User\UserDetailsBasicType::class, $user, []);
            $jmsPutGroups = ['user_details_basic'];
        } elseif ($this->isGranted(EntityDir\User::ROLE_LAY_DEPUTY)) {
            $form = $this->createForm(FormDir\Settings\ProfileType::class, $user, ['validation_groups' => ['user_details_full']]);
            $jmsPutGroups = ['user_details_full'];
        } elseif ($this->isGranted(EntityDir\User::ROLE_ORG)) {
            $form = $this->createForm(FormDir\Settings\ProfileType::class, $user, ['validation_groups' => ['user_details_org', 'profile_org']]);
            $jmsPutGroups = ['user_details_org', 'profile_org'];
        } else {
            throw $this->createAccessDeniedException('User role not recognised');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deputy = $form->getData();

            if ($form->has('removeAdmin') && !empty($form->get('removeAdmin')->getData())) {
                $oldRole = $user->getRoleName();
                $newRole = $this->determineNoAdminRole();

                $event = (new AuditEvents($this->dateTimeProvider))
                    ->roleChanged(
                        AuditEvents::TRIGGER_DEPUTY_USER,
                        $oldRole,
                        $newRole,
                        $user->getEmail(),
                        $user->getEmail()
                    );

                $user->setRoleName($newRole);

                $this->addFlash('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');

                $redirectRoute = $this->generateUrl('logout');
            } else {
                $this->addFlash('notice', 'Your account details have been updated');

                if ('declaration' === $request->get('from') && null !== $request->get('rid')) {
                    $redirectRoute = $this->generateUrl('report_declaration', ['reportId' => $request->get('rid')]);
                } else if ($user->isDeputyPA() || $user->isDeputyProf()) {
                    $redirectRoute = $this->generateUrl('org_profile_show');
                } else {
                    $redirectRoute = $this->generateUrl('user_show');
                }
            }

            try {
                $this->getRestClient()->put('user/' . $user->getId(), $deputy, $jmsPutGroups);

                if (isset($event, $oldRole, $newRole) && $oldRole !== $newRole) {
                    $this->logger->notice('', $event);
                }

                if ($user->isLayDeputy()) {
                    $hydratedDeputy = $this->getUserWithData(['user-clients', 'client']);

                    $updateDeputyDetailsEmail = $this->mailFactory->createUpdateDeputyDetailsEmail($hydratedDeputy);
                    $this->mailSender->send($updateDeputyDetailsEmail);
                }

                return $this->redirect($redirectRoute);
            } catch (\Throwable $e) {
                if ($e->getCode() == 422 && $form->get('email')) {
                    $form->get('email')->addError(new FormError($this->translator->trans('user.email.alreadyUsed', [], 'validators')));
                }
            }
        }

        return [
            'user'   => $user,
            'form'   => $form->createView(),
            'client_validated' => false // to allow change of name/postcode/email
        ];
    }

    /**
     * If remove admin permission, return the new role for the user. Specifically added to prevent named PA deputies
     * becoming Professional team members.
     *
     * @throws AccessDeniedException
     * @return string
     *
     */
    private function determineNoAdminRole()
    {
        if ($this->isGranted(EntityDir\User::ROLE_PA_ADMIN)) {
            return EntityDir\User::ROLE_PA_TEAM_MEMBER;
        } elseif ($this->isGranted(EntityDir\User::ROLE_PROF_ADMIN)) {
            return EntityDir\User::ROLE_PROF_TEAM_MEMBER;
        }
        $this->createAccessDeniedException('User role not recognised');
    }
}

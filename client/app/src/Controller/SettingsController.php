<?php

namespace App\Controller;

use App\Entity;
use App\Form;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Redirector;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserApi $userApi,
        private readonly RestClient $restClient,
    ) {
    }

    #[Route(path: '/deputyship-details', name: 'account_settings')]
    #[Route(path: '/org/settings', name: 'org_settings')]
    #[Template('@App/Settings/index.html.twig')]
    public function indexAction(Redirector $redirector): array|RedirectResponse
    {
        if ($this->getUser()->isDeputyOrg()) {
            $user = $this->userApi->getUserWithData(['user-organisations', 'organisation']);

            return [
                'hasOrganisations' => count($user->getOrganisations()),
            ];
        }

        // redirect if user has missing details or is on wrong page
        $user = $this->userApi->getUserWithData();
        if ($route = $redirector->getCorrectRouteIfDifferent($user, 'account_settings')) {
            return $this->redirectToRoute($route);
        }

        return [];
    }

    #[Route(path: '/deputyship-details/your-details/change-password', name: 'user_password_edit')]
    #[Route(path: '/org/settings/your-details/change-password', name: 'org_profile_password_edit')]
    #[Template('@App/Settings/passwordEdit.html.twig')]
    public function passwordEditAction(Request $request): RedirectResponse|array
    {
        $user = $this->userApi->getUserWithData();

        $form = $this->createForm(Form\ChangePasswordType::class, $user, [
            'mapped' => true,
            'error_bubbling' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $request->request->all('change_password')['password']['first'];
            $this->restClient->put('user/' . $user->getId() . '/set-password', json_encode([
                'password' => $plainPassword,
            ]));
            $request->getSession()->set('login-context', 'password-update');

            $successRoute = $this->getUser()->isDeputyOrg() ? 'org_settings' : 'account_settings';

            return $this->redirect($this->generateUrl($successRoute));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/org/settings/your-details/change-email', name: 'org_profile_email_edit')]
    #[Template('@App/Settings/emailEdit.html.twig')]
    public function emailEditAction(Request $request): RedirectResponse|array
    {
        $user = $this->userApi->getUserWithData();

        $form = $this->createForm(Form\ChangeEmailType::class, $user, [
            'mapped' => false,
            'error_bubbling' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updatedEmail = $request->request->get('change_email')['new_email']['first'];

            $this->restClient->put('user/' . $user->getId() . '/update-email', json_encode([
                'updated_email' => $updatedEmail,
            ]));

            $request->getSession()->set('login-context', 'email-update');

            $successRoute = $this->getUser()->isDeputyOrg() ? 'org_settings' : 'account_settings';

            return $this->redirect($this->generateUrl($successRoute));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Display the Your details page.
     **/
    #[Route(path: '/deputyship-details/your-details', name: 'user_show')]
    #[Route(path: '/org/settings/your-details', name: 'org_profile_show')]
    #[Template('@App/Settings/profile.html.twig')]
    public function profileAction(): array
    {
        return [
            'user' => $this->getUser(),
        ];
    }

    /**
     * Change your own details.
     *
     * @throw AccessDeniedException
     **/
    #[Route(path: '/deputyship-details/your-details/edit', name: 'user_edit')]
    #[Route(path: '/org/settings/your-details/edit', name: 'org_profile_edit')]
    #[Template('@App/Settings/profileEdit.html.twig')]
    public function profileEditAction(Request $request): RedirectResponse|array
    {
        $preUpdateDeputy = $this->userApi->getUserWithData(['user-clients', 'client']);

        if ($this->isGranted(Entity\User::ROLE_ADMIN) || $this->isGranted(Entity\User::ROLE_AD)) {
            $form = $this->createForm(Form\User\UserDetailsBasicType::class, clone $preUpdateDeputy, []);
            $jmsPutGroups = ['user_details_basic'];
        } elseif ($this->isGranted(Entity\User::ROLE_LAY_DEPUTY)) {
            $form = $this->createForm(Form\Settings\ProfileType::class, clone $preUpdateDeputy, ['validation_groups' => ['user_details_full']]);
            $jmsPutGroups = ['user_details_full'];
        } elseif ($this->isGranted(Entity\User::ROLE_ORG)) {
            $form = $this->createForm(Form\Settings\ProfileType::class, clone $preUpdateDeputy, ['validation_groups' => ['user_details_org', 'profile_org']]);
            $jmsPutGroups = ['user_details_org', 'profile_org'];
        } else {
            throw $this->createAccessDeniedException('User role not recognised');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postUpdateDeputy = $form->getData();
            $newRole = $this->determineNoAdminRole();

            if ($form->has('removeAdmin') && !empty($form->get('removeAdmin')->getData())) {
                $postUpdateDeputy->setRoleName($newRole);

                $this->addFlash('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');

                $redirectRoute = $this->generateUrl('app_logout');
            } else {
                $this->addFlash('notice', 'Your account details have been updated');

                if ('declaration' === $request->get('from') && null !== $request->get('rid')) {
                    $redirectRoute = $this->generateUrl('report_confirm_details', ['reportId' => $request->get('rid')]);
                } elseif ($postUpdateDeputy->isDeputyPA() || $postUpdateDeputy->isDeputyProf()) {
                    $redirectRoute = $this->generateUrl('org_profile_show');
                } else {
                    $redirectRoute = $this->generateUrl('user_show');
                }
            }

            try {
                $this->userApi->update($preUpdateDeputy, $postUpdateDeputy, AuditEvents::TRIGGER_DEPUTY_USER_EDIT_SELF, $jmsPutGroups);

                return $this->redirect($redirectRoute);
            } catch (\Throwable $e) {
                if (422 === $e->getCode() && $form->get('email')) {
                    $form->get('email')->addError(new FormError($this->translator->trans('user.email.alreadyUsed', [], 'validators')));
                }
            }
        }

        return [
            'user' => $preUpdateDeputy,
            'form' => $form->createView(),
            'client_validated' => false, // to allow change of name/postcode/email
        ];
    }

    /**
     * If remove admin permission, return the new role for the user. Specifically added to prevent named PA deputies
     * becoming Professional team members.
     */
    private function determineNoAdminRole(): string
    {
        if ($this->isGranted(Entity\User::ROLE_PA_ADMIN)) {
            return Entity\User::ROLE_PA_TEAM_MEMBER;
        } elseif ($this->isGranted(Entity\User::ROLE_PROF_ADMIN)) {
            return Entity\User::ROLE_PROF_TEAM_MEMBER;
        }

        throw $this->createAccessDeniedException('User role not recognised');
    }
}

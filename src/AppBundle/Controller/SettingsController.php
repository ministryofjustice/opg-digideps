<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends AbstractController
{
    /**
     * @Route("/deputyship-details", name="account_settings")
     * @Route("/pa/settings", name="pa_settings")
     * @Template()
     **/
    public function indexAction()
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData(['user-clients', 'client', 'report']);
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'account_settings')) {
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
     * @Route("/pa/settings/your-details/change-password", name="pa_profile_password_edit")
     * @Template()
     */
    public function passwordEditAction(Request $request)
    {
        $user = $this->getUserWithData();

        $form = $this->createForm(FormDir\ChangePasswordType::class, $user, ['mapped' => false, 'error_bubbling' => true]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $plainPassword = $request->request->get('change_password')['plain_password']['first'];
            $this->getRestClient()->put('user/' . $user->getId() . '/set-password', json_encode([
                'password_plain' => $plainPassword,
            ]));
            $request->getSession()->getFlashBag()->add('notice', 'Password edited');

            $successRoute = $this->getUser()->isDeputyOrg() ? 'pa_settings' : 'user_password_edit_done';
            return $this->redirect($this->generateUrl($successRoute));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/deputyship-details/your-details/change-password/done", name="user_password_edit_done")
     * @Template()
     */
    public function passwordEditDoneAction(Request $request)
    {
        return [];
    }

    /**
     * - display the Your details page
     *
     * @Route("/deputyship-details/your-details", name="user_show")
     * @Route("/pa/settings/your-details", name="pa_profile_show")
     * @Template()
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
     * @Route("/pa/settings/your-details/edit", name="pa_profile_edit")
     * @Template()
     * @throw AccessDeniedException
     **/
    public function profileEditAction(Request $request)
    {
        $user = $this->getUserWithData();

        if ($this->isGranted(EntityDir\User::ROLE_ADMIN) || $this->isGranted(EntityDir\User::ROLE_AD)) {
            $form = $this->createForm(FormDir\User\UserDetailsBasicType::class, $user, []);
            $jmsPutGroups = ['user_details_basic'];
        } else if ($this->isGranted(EntityDir\User::ROLE_LAY_DEPUTY)) {
            $form = $this->createForm(FormDir\Settings\ProfileType::class, $user, ['validation_groups' => ['user_details_full']]);
            $jmsPutGroups = ['user_details_full'];
        } else if ($this->isGranted(EntityDir\User::ROLE_ORG)) {
            $form = $this->createForm(FormDir\Settings\ProfileType::class, $user, ['validation_groups' => ['user_details_pa', 'profile_pa']]);
            $jmsPutGroups = ['user_details_pa', 'profile_pa'];
        } else {
            $this->createAccessDeniedException('User role not recognised');
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            if ($form->has('removeAdmin') && !empty($form->get('removeAdmin')->getData())) {
                $newRole = $this->determineNoAdminRole();
                $user->setRoleName($newRole);
                $request->getSession()->getFlashBag()->add('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');
                $redirectRoute = 'logout';
            } else {
                $request->getSession()->getFlashBag()->add('notice', 'Your account details have been updated');
                $redirectRoute = ($user->isDeputyPA() || $user->isDeputyProf())
                    ? 'pa_profile_show'
                    : 'user_show';
            }

            $this->getRestClient()->put('user/' . $user->getId(), $formData, $jmsPutGroups);
            return $this->redirectToRoute($redirectRoute);
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
     * @return string
     *
     * @throws AccessDeniedException
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

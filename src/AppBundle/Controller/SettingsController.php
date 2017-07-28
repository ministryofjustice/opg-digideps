<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $user = $this->getUserWithData(['client', 'report']);
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

        $form = $this->createForm(new FormDir\ChangePasswordType(), $user, ['mapped' => false, 'error_bubbling' => true]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $plainPassword = $request->request->get('change_password')['plain_password']['first'];
            $this->getRestClient()->put('user/' . $user->getId() . '/set-password', json_encode([
                'password_plain' => $plainPassword,
            ]));
            $request->getSession()->getFlashBag()->add('notice', 'Password edited');

            $successRoute = $this->getUser()->isDeputyPA() ? 'pa_settings' : 'user_password_edit_done';
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
     **/
    public function profileEditAction(Request $request)
    {
        $user = $this->getUserWithData();
        list($formType, $jmsPutGroups) = $this->getFormAndJmsGroupBasedOnUserRole();
        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            if ($form->has('removeAdmin') && !empty($form->get('removeAdmin')->getData())) {
                $user->setRoleName('ROLE_PA_TEAM_MEMBER');
                $request->getSession()->getFlashBag()->add('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');
                $redirectRoute = 'logout';
            } else {
                $request->getSession()->getFlashBag()->add('notice', 'Your account details have been updated');
                $redirectRoute = $user->isDeputyPA()
                    ? 'pa_profile_show'
                    : 'user_show';
            }

            $this->getRestClient()->put('user/' . $user->getId(), $formData, $jmsPutGroups);
            return $this->redirectToRoute($redirectRoute);

        }

        return [
            'user'   => $user,
            'form'   => $form->createView(),
        ];
    }


    private function getFormAndJmsGroupBasedOnUserRole()
    {
        switch ($this->getUser()->getRoleName()) {
            case EntityDir\User::ROLE_ADMIN:
            case EntityDir\User::ROLE_AD:
                $formAndGroup = [new FormDir\User\UserDetailsBasicType(), ['user_details_basic']];
                break;
            case EntityDir\User::ROLE_LAY_DEPUTY:
                $formAndGroup = [new FormDir\Settings\ProfileType(['user_details_full']), ['user_details_full']];
                break;
            case EntityDir\User::ROLE_PA:
            case EntityDir\User::ROLE_PA_ADMIN:
            case EntityDir\User::ROLE_PA_TEAM_MEMBER:
                $formAndGroup = [new FormDir\Settings\ProfileType(['user_details_pa']), ['user_details_pa']];
                break;
        }
        return $formAndGroup;
    }
}
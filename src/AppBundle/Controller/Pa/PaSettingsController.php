<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/pa/settings")
 */
class PaSettingsController extends AbstractController
{
    /**
     * @Route("/", name="pa_settings")
     * @Template("AppBundle:Pa/Settings:index.html.twig")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Display the profile summary page
     *
     * @Route("/your-details", name="pa_profile_show")
     * @Template("AppBundle:Pa/Settings:profile.html.twig")
     **/
    public function profileAction()
    {
        return [
            'user' => $this->getUser()
        ];
    }

    /**
     * Display the profile edit form
     *
     * @Route("/your-details/update-your-account", name="pa_profile_edit")
     * @Template("AppBundle:Pa/Settings:profileEdit.html.twig")
     **/
    public function profileEditAction(Request $request)
    {
        $loggedInUser = $this->getUser();
        $form = $this->createForm(new FormDir\Pa\ProfileType($loggedInUser), $loggedInUser);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            try {
                if ($form->has('removeAdmin') && !empty($form->get('removeAdmin')->getData())) {
                    $user->setRoleName('ROLE_PA_TEAM_MEMBER');
                    $request->getSession()->getFlashBag()->add('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');
                    $redirectRoute = 'logout';
                } else {
                    $request->getSession()->getFlashBag()->add('notice', 'Your account details have been updated');
                    $redirectRoute = 'pa_profile_show';
                }

                $this->getRestClient()->put('user/' . $user->getId(), $user, ['pa_team_add'], 'User');

                return $this->redirectToRoute($redirectRoute);
            } catch (\Exception $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'pa-team')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

}

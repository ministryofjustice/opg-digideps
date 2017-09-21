<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class CoDeputyController extends AbstractController
{

    /**
     * @Route("/codeputy/{clientId}/add", name="add_co_deputy")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $loggedInUser = $this->getUserWithData(['user-clients', 'client']);
        $invitedUser = new EntityDir\User();

        $form = $this->createForm(new FormDir\CoDeputyType(), $invitedUser);

        $backLink = $loggedInUser->isOdrEnabled() ?
            $this->generateUrl('odr_index')
            :$this->generateUrl('lay_home');

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $invitedUser = $this->getRestClient()->post('codeputy/add', $form->getData(), ['codeputy'], 'User');

                // Regular deputies should become coDeputies via a CSV import, but at least for testing handle the change from non co-dep to co-dep here
                $this->getRestClient()->put('user/'.$loggedInUser->getId(), ['co_deputy_client_confirmed' => true], []);

                $invitationEmail = $this->getMailFactory()->createCoDeputyInvitationEmail($invitedUser, $loggedInUser);
                $this->getMailSender()->send($invitationEmail);

                $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation has been sent');

                return $this->redirect($backLink);
            } catch (\Exception $e) {
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
            'client' => $this->getFirstClient()
        ];
    }


    /**
     * @Route("/codeputy/re-invite/{email}", name="codep_resend_activation")
     * @Template()
     **/
    public function resendActivationAction(Request $request, $email)
    {
        $loggedInUser = $this->getUserWithData(['user-clients', 'client']);
        $invitedUser = $this->getRestClient()->userRecreateToken($email, 'pass-reset');

        $form = $this->createForm(new FormDir\CoDeputyType(), $invitedUser);

        $backLink = $loggedInUser->isOdrEnabled() ?
            $this->generateUrl('odr_index')
            :$this->generateUrl('lay_home');

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                //email was updated on the fly
                if ($form->getData()->getEmail() != $email) {
                    $this->getRestClient()->put('codeputy/'.$invitedUser->getId(), $form->getData(), []);
                }
                $invitationEmail = $this->getMailFactory()->createCoDeputyInvitationEmail($invitedUser, $loggedInUser);
                $this->getMailSender()->send($invitationEmail);
                $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation was re-sent');

                return $this->redirect($backLink);
            } catch (\Exception $e) {
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

<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\SelfRegisterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class CoDeputyController extends AbstractController
{
    /**
     * @Route("/codeputy/verification", name="codep_verification")
     * @Template()
     */
    public function verificationAction(Request $request)
    {
        $user = $this->getUserWithData(['user', 'user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'codep_verification')) {
            return $this->redirectToRoute($route);
        }

        $form = $this->createForm(new FormDir\CoDeputyVerificationType(), $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()){

            // get client validation errors, if any, and add to the form
            $client = new EntityDir\Client();
            $client->setLastName($form['clientLastname']->getData());
            $client->setCaseNumber($form['clientCaseNumber']->getData());
            $errors = $this->get('validator')->validate($client, ['verify-codeputy']);
            foreach($errors as $error) {
                $clientProperty = $error->getPropertyPath();
                $form->get('client'.ucfirst($clientProperty))->addError(new FormError($error->getMessage()));
            }

            if ($form->isValid()) {

                $selfRegisterData = new SelfRegisterData();
                $selfRegisterData->setFirstname($form['firstname']->getData());
                $selfRegisterData->setLastname($form['lastname']->getData());
                $selfRegisterData->setEmail($form['email']->getData());
                $selfRegisterData->setPostcode($form['addressPostcode']->getData());
                $selfRegisterData->setClientLastname($form['clientLastname']->getData());
                $selfRegisterData->setCaseNumber($form['clientCaseNumber']->getData());

                // validate against casRec
                try {
                    $this->getRestClient()->apiCall('post', 'selfregister/verifycodeputy', $selfRegisterData, 'array', [], false);
                    $user->setCoDeputyClientConfirmed(true);
                    $this->getRestClient()->put('user/' . $user->getId(), $user);
                    return $this->redirect($this->generateUrl('homepage'));
                } catch (\Exception $e) {
                    $translator = $this->get('translator');
                    switch ((int) $e->getCode()) {
                        case 422:
                            $form->get('email')->addError(new FormError($translator->trans('email.first.existingError', [], 'register')));
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
            'user' => $user
        ];
    }

    /**
     * @Route("/codeputy/{clientId}/add", name="add_co_deputy")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $loggedInUser = $this->getUserWithData(['user-clients', 'client']);

        // redirect if user has missing details or is on wrong page
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($loggedInUser, 'add_co_deputy')) {
            return $this->redirectToRoute($route);
        }

        $invitedUser = new EntityDir\User();

        $form = $this->createForm(new FormDir\CoDeputyInviteType(), $invitedUser);

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

        $form = $this->createForm(new FormDir\CoDeputyInviteType(), $invitedUser, ['translation_domain' => 'codeputy-resend-invite']);

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

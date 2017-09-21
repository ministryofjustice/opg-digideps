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
        $client = $loggedInUser->getClients()[0];
        $invitedUser = new EntityDir\User();

        $form = $this->createForm(new FormDir\CoDeputyType($client), $invitedUser);

        $backLink = $this->getUser()->isOdrEnabled() ?
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
            'client' => $client,
            'form' => $form->createView(),
            'backLink' => $backLink
        ];
    }
}

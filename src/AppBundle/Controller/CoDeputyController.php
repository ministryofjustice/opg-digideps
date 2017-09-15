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
        $user = $this->getUserWithData(['user-clients', 'client']);
        $client = $user->getClients()[0];
        $user = new EntityDir\User();

        $form = $this->createForm(new FormDir\CoDeputyType($client), $user);

        // @to-do remove hard coded 102
        $backLink = $this->getUser()->isOdrEnabled() ?
            $this->generateUrl('odr_index')
            :$this->generateUrl('reports', ['type' => '102']);

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $newUser = $this->getRestClient()->post('codeputy/add', $form->getData(), ['codeputy'], 'User');

                $invitationEmail = $this->getMailFactory()->createCoDeputyInvitationEmail($newUser);
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

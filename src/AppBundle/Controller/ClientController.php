<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ClientController extends AbstractController
{
    /**
     * @Route("/deputyship-details/your-client", name="client_show")
     * @Template()
     */
    public function showAction(Request $request)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData();
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'client_show')) {
            return $this->redirectToRoute($route);
        }

        $client = $this->getFirstClient();

        return [
            'client' => $client,
            'lastSignedIn' => $request->getSession()->get('lastLoggedIn'),
        ];
    }

    /**
     * @Route("/deputyship-details/your-client/edit", name="client_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        $from = $request->get('from');
        $client = $this->getFirstClient();

        $form = $this->createForm(FormDir\ClientType::class, $client, [
            'action' => $this->generateUrl('client_edit', ['action' => 'edit', 'from' => $from]),
            'validation_groups' => ['lay-deputy-client-edit']
        ]);

        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, ['edit']);
            $request->getSession()->getFlashBag()->add('notice', htmlentities($client->getFirstname()) . "'s data edited");

            $user = $this->getUserWithData(['user-clients', 'client']);

            if ($user->isLayDeputy()) {
                $addressUpdateEmail = $this->getMailFactory()->createAddressUpdateEmail($form->getData(), $user, 'client');
                $this->getMailSender()->send($addressUpdateEmail, ['html']);
            }

            if ($from === 'declaration') {
                return $this->redirect($this->generateUrl('report_declaration', ['reportId' => $client->getActiveReport()->getId()]));
            }

            return $this->redirect($this->generateUrl('client_show'));
        }

        return [
            'client' => $client,
            'form' => $form->createView(),
            'lastSignedIn' => $request->getSession()->get('lastLoggedIn'),
        ];
    }

    /**
     * @Route("/client/add", name="client_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData();
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'client_add')) {
            return $this->redirectToRoute($route);
        }

        $client = $this->getFirstClient();
        if (!empty($client)) {
            // update existing client
            $client = $this->getRestClient()->get('client/' . $client->getId(), 'Client', ['client', 'report-id', 'current-report']);
            $method = 'put';
            $client_validated = true;
        } else {
            // new client
            $client = new EntityDir\Client();
            $method = 'post';
            $client_validated = false;
        }

        $form = $this->createForm(FormDir\ClientType::class, $client);

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                // validate against casRec
                $this->getRestClient()->apiCall('post', 'casrec/verify', $client, 'array', []);

                // $method is set above to either post or put
                $response =  $this->getRestClient()->$method('client/upsert', $form->getData());

                $url = $this->getUser()->isNdrEnabled()
                    ? $this->generateUrl('ndr_index')
                    : $this->generateUrl('report_create', ['clientId' => $response['id']]);
                return $this->redirect($url);
            } catch (\Exception $e) {
                $translator = $this->get('translator');
                switch ((int) $e->getCode()) {
                    case 400:
                        $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                }
                $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        return [
            'form' => $form->createView(),
            'client_validated' => $client_validated,
            'client' => $client
        ];
    }
}

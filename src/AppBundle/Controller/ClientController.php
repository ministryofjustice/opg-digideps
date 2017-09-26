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
        $client = $this->getFirstClient();

        $form = $this->createForm(new FormDir\ClientType(), $client, ['action' => $this->generateUrl('client_edit', ['action' => 'edit'])]);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, ['edit']);
            $request->getSession()->getFlashBag()->add('notice', htmlentities($client->getFirstname()) . "'s data edited");

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
            $method = 'put';
            $client = $this->getRestClient()->get('client/' . $client->getId(), 'Client', ['client', 'report-id', 'current-report']);
        } else {
            // new client
            $method = 'post';
            $client = new EntityDir\Client();
            $client->addUser($this->getUser()->getId());
        }

        $form = $this->createForm(new FormDir\ClientType(), $client);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = ($method === 'post')
                      ? $this->getRestClient()->post('client/upsert', $form->getData())
                      : $this->getRestClient()->put('client/upsert', $form->getData());

            $url = $this->getUser()->isOdrEnabled() ?
                $this->generateUrl('odr_index')
                 :$this->generateUrl('report_create', ['clientId' => $response['id']]);

            return $this->redirect($url);
        }

        return ['form' => $form->createView()];
    }

}
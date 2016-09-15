<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ClientController extends AbstractController
{
    /**
     * @Route("/user-account/client-show", name="client_show")
     * @Template()
     */
    public function showAction(Request $request)
    {
        $client = $this->getFirstClient();

        return [
            'client' => $client,
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn'),
        ];
    }

    /**
     * @Route("/user-account/client-edit", name="client_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        $client = $this->getFirstClient();

        $form = $this->createForm(new FormDir\ClientType($this->getRestClient()), $client, ['action' => $this->generateUrl('client_edit', ['action' => 'edit'])]);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, ['edit']);

            return $this->redirect($this->generateUrl('client_show'));
        }

        return [
            'client' => $client,
            'form' => $form->createView(),
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn'),
        ];
    }

    /**
     * @Route("/client/add", name="client_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $user = $this->getUserWithData(['user', 'client']);
        $clients = $user->getClients();

        if (!empty($clients) && $clients[0] instanceof EntityDir\Client) {
            // update existing client
            $method = 'put';
            $client = $clients[0]; //existing client
            $client = $this->getRestClient()->get('client/'.$client->getId(), 'Client');
        } else {
            // new client
            $method = 'post';
            $client = new EntityDir\Client();
            $client->addUser($this->getUser()->getId());
        }

        $allowedCot = $this->getAllowedCourtOrderTypeChoiceOptions(); //TODO inject into form
        $form = $this->createForm(new FormDir\ClientType($this->getRestClient()), $client);

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

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
        $clients = $this->getUser()->getClients();

        $client = !empty($clients) ? $clients[0] : null;

        $report = new EntityDir\Report();
        $report->setClient($client);

        return [
            'client' => $client,
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn'),
        ];
    }

    /**
     * @Route("/user-account/client-edit", name="client_edit")
     * @Template()
     */
    public function editAction()
    {
        $clients = $this->getUser()->getClients();
        $request = $this->getRequest();

        $client = !empty($clients) ? $clients[0] : null;

        $report = new EntityDir\Report();
        $report->setClient($client);

        $form = $this->createForm(new FormDir\ClientType($this->getRestClient()), $client, ['action' => $this->generateUrl('client_edit', ['action' => 'edit'])]);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, [
                 'deserialise_group' => 'edit',
            ]);

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
    public function addAction()
    {
        $request = $this->getRequest();

        $clients = $this->getUser()->getClients();
        if (!empty($clients) && $clients[0] instanceof EntityDir\Client) {
            // update existing client
            $method = 'put';
            $client = $clients[0]; //existing client
        } else {
            // new client
            $method = 'post';
            $client = new EntityDir\Client();
            $client->addUser($this->getUser()->getId());
        }

        $allowedCot = $this->getAllowedCourtOrderTypeChoiceOptions();
        $form = $this->createForm(new FormDir\ClientType($this->getRestClient()), $client);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = ($method === 'post')
                      ? $this->getRestClient()->post('client/upsert', $form->getData())
                      : $this->getRestClient()->put('client/upsert', $form->getData());

            return $this->redirect($this->generateUrl('odr_index'));
        }

        return ['form' => $form->createView()];
    }
}

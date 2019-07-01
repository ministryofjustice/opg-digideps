<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/contact/")
 */
class ClientContactController extends AbstractController
{
    private static $jmsGroups = [
        'contacts'
    ];

    /**
     * @Route("add", name="clientcontact_add")
     * @Template("AppBundle:Org/ClientProfile:addContact.html.twig")
     */
    public function addAction(Request $request)
    {
        $clientId = $request->get('clientId');

        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'client-users', 'report-id', 'current-report', 'user']);
        if (!isset($clientId) || !($client instanceof EntityDir\Client)) {
            throw $this->createNotFoundException('Client not found');
        }

        $this->denyAccessUnlessGranted('add-client-contact', $client, 'Access denied');

        $clientContact = new EntityDir\ClientContact($client);

        $form = $this->createForm(FormDir\Org\ClientContactType::class, $clientContact);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->post('clients/' . $client->getId() . '/clientcontacts', $form->getData(), ['add_clientcontact']
            );
            $request->getSession()->getFlashBag()->add('notice', 'The contact has been added');

            return $this->redirect($this->generateClientProfileLink($client));
        }

        return [
            'form'  => $form->createView(),
            'client' => $client,
            'report' => $client->getCurrentReport(),
            'backLink' => $this->generateClientProfileLink($client)
        ];
    }

    /**
     * @Route("{id}/edit", name="clientcontact_edit")
     * @Template("AppBundle:Org/ClientProfile:editContact.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $clientContact = $this->getContactById($id);
        $client = $clientContact->getClient();
        $backLink = $this->generateClientProfileLink($client);

        $this->denyAccessUnlessGranted('edit-client-contact', $client, 'Access denied');

        $form = $this->createForm(FormDir\Org\ClientContactType::class, $clientContact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('/clientcontacts/' . $id, $form->getData(), ['edit_clientcontact']
            );
            $request->getSession()->getFlashBag()->add('notice', 'The contact has been updated');
            return $this->redirect($backLink);
        }

        return [
            'form'     => $form->createView(),
            'client'   => $client,
            'report'   => $client->getCurrentReport(),
            'backLink' => $backLink
        ];
    }

    /**
     * @Route("{id}/delete", name="clientcontact_delete")
     * @Template("AppBundle:Org/ClientProfile:deleteContactConfirm.html.twig")
     */
    public function deleteConfirmAction(Request $request, $id, $confirmed = false)
    {
        $clientContact = $this->getContactById($id);
        $client = $clientContact->getClient();
        $this->denyAccessUnlessGranted('delete-client-contact', $client, 'Access denied');

        $client = $clientContact->getClient();

        return [
            'report'   => $client->getCurrentReport(),
            'contact'  => $clientContact,
            'client'   => $client,
            'backLink' => $this->generateClientProfileLink($client)
        ];
    }

    /**
     * @Route("{id}/delete/confirm", name="clientcontact_delete_confirm")
     * @Template("AppBundle:Org/ClientProfile:deleteContactConfirm.html.twig")
     */
    public function deleteConfirmedAction(Request $request, $id)
    {
        $clientContact = $this->getContactById($id);
        $client = $clientContact->getClient();

        $this->denyAccessUnlessGranted('delete-client-contact', $client, 'Access denied');
        try {
            $this->getRestClient()->delete('clientcontacts/' . $id);
            $request->getSession()->getFlashBag()->add('notice', 'Contact has been removed');
        } catch (\Throwable $e) {
            $this->get('logger')->error($e->getMessage());
            $request->getSession()->getFlashBag()->add(
                'error',
                'Client contact could not be removed'
            );
        }

        return $this->redirect($this->generateClientProfileLink($clientContact->getClient()));
    }

    /**
     * @param $id
     * @return mixed
     */
    private function getContactById($id)
    {
        return $this->getRestClient()->get(
            'clientcontacts/' . $id,
            'ClientContact',
            ['clientcontact', 'clientcontact-client', 'client', 'client-users', 'current-report', 'report-id', 'user']
        );
    }
}

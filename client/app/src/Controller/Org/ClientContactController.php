<?php

namespace App\Controller\Org;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\RestClient;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("/contact/")
 */
class ClientContactController extends AbstractController
{
    private static $jmsGroups = [
        'contacts',
    ];

    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        ClientApi $clientApi,
        RestClient $restClient
    ) {
        $this->clientApi = $clientApi;
        $this->restClient = $restClient;
    }

    /**
     * @Route("add", name="clientcontact_add")
     * @Template("@App/Org/ClientProfile/addContact.html.twig")
     *
     * @throws Exception
     */
    public function addAction(Request $request)
    {
        $clientId = $request->get('clientId');

        /** @var $client EntityDir\Client */
        $client = $this->restClient->get('client/'.$clientId, 'Client', ['client', 'client-users', 'report-id', 'current-report', 'user']);
        if (!isset($clientId) || !($client instanceof EntityDir\Client)) {
            throw $this->createNotFoundException('Client not found');
        }

        $this->denyAccessUnlessGranted('add-client-contact', $client, 'Access denied');

        $clientContact = new EntityDir\ClientContact($client);

        $form = $this->createForm(FormDir\Org\ClientContactType::class, $clientContact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->post(
                'clients/'.$client->getId().'/clientcontacts',
                $form->getData(),
                ['add_clientcontact']
            );
            $request->getSession()->getFlashBag()->add('notice', 'The contact has been added');

            return $this->redirect($this->clientApi->generateClientProfileLink($client));
        }

        return [
            'form' => $form->createView(),
            'client' => $client,
            'report' => $client->getCurrentReport(),
            'backLink' => $this->clientApi->generateClientProfileLink($client),
        ];
    }

    /**
     * @Route("{id}/edit", name="clientcontact_edit")
     * @Template("@App/Org/ClientProfile/editContact.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $clientContact = $this->getContactById($id);
        $client = $clientContact->getClient();
        $backLink = $this->clientApi->generateClientProfileLink($client);

        $this->denyAccessUnlessGranted('edit-client-contact', $client, 'Access denied');

        $form = $this->createForm(FormDir\Org\ClientContactType::class, $clientContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put(
                '/clientcontacts/'.$id,
                $form->getData(),
                ['edit_clientcontact']
            );
            $request->getSession()->getFlashBag()->add('notice', 'The contact has been updated');

            return $this->redirect($backLink);
        }

        return [
            'form' => $form->createView(),
            'client' => $client,
            'report' => $client->getCurrentReport(),
            'backLink' => $backLink,
        ];
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    private function getContactById($id)
    {
        return $this->restClient->get(
            'clientcontacts/'.$id,
            'ClientContact',
            ['clientcontact', 'clientcontact-client', 'client', 'client-users', 'current-report', 'report-id', 'user']
        );
    }

    /**
     * @Route("{id}/delete", name="clientcontact_delete")
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @throws Exception
     */
    public function deleteConfirmAction(Request $request, $id, LoggerInterface $logger, $confirmed = false)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $clientContact = $this->getContactById($id);
        $client = $clientContact->getClient();
        $this->denyAccessUnlessGranted('delete-client-contact', $client, 'Access denied');

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->restClient->delete('clientcontacts/'.$id);
                $request->getSession()->getFlashBag()->add('notice', 'Contact has been removed');
            } catch (Throwable $e) {
                $logger->error($e->getMessage());
                $request->getSession()->getFlashBag()->add(
                    'error',
                    'Client contact could not be removed'
                );
            }

            return $this->redirect($this->clientApi->generateClientProfileLink($clientContact->getClient()));
        }

        $client = $clientContact->getClient();

        return [
            'translationDomain' => 'client-contacts',
            'report' => $client->getCurrentReport(),
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.name', 'value' => $clientContact->getFirstname().' '.$clientContact->getLastName()],
                ['label' => 'deletePage.summary.orgName', 'value' => $clientContact->getOrgName()],
            ],
            'backLink' => $this->clientApi->generateClientProfileLink($client),
        ];
    }
}

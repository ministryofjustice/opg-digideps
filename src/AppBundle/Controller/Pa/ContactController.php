<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/contact/")
 */
class ContactController extends AbstractController
{
    private static $jmsGroups = [
        'contacts'
    ];

    /**
     * @Route("add", name="add_contact")
     * @Template("AppBundle:Pa/ClientProfile:addContact.html.twig")
     */
    public function addAction(Request $request)
    {
        $clientId = $request->get('clientId');

        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report', 'user']);

        $this->denyAccessUnlessGranted('add-note', $client, 'Access denied');

        $report = $client->getCurrentReport();

        $note = new EntityDir\Note($client);

        $form = $this->createForm(
            new FormDir\Pa\NoteType($this->get('translator')),
            $note
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $note = $form->getData();

            $this->getRestClient()->post('note/' . $client->getId(), $note, ['add_note']);
            $request->getSession()->getFlashBag()->add('notice', 'The note has been added');

            return $this->redirect($this->generateClientProfileLink($note->getClient()));
        }

        return [
            'form'  => $form->createView(),
            'client' => $client,
            'report' => $report,
            'backLink' => $this->generateClientProfileLink($note->getClient())
        ];
    }
}

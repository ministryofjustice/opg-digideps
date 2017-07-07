<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/note/")
 */
class NoteController extends AbstractController
{
    private static $jmsGroups = [
        'notes'
    ];

    /**
     * @Route("add", name="add_note")
     * @Template("AppBundle:Pa/ClientProfile:addNote.html.twig")
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

    /**
     * @Route("{noteId}/edit", name="edit_note")
     * @Template("AppBundle:Pa/ClientProfile:editNote.html.twig")
     */
    public function editAction(Request $request, $noteId)
    {
        /** @var EntityDir\Note $note */
        $note = $this->getNote($noteId);

        $this->denyAccessUnlessGranted('edit-note', $note, 'Access denied');

        $form = $this->createForm(
            new FormDir\Pa\NoteType($this->get('translator')),
            $note
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $note = $form->getData();

            $this->getRestClient()->put('note/' . $noteId, $note, ['add_note']);
            $request->getSession()->getFlashBag()->add(
                'notice',
                'The note has been edited'
            );

            return $this->redirect($this->generateClientProfileLink($note->getClient()));
        }

        return [
            'report'  => $note->getClient()->getCurrentReport(),
            'form'  => $form->createView(),
            'client' => $note->getClient(),
            'backLink' => $this->generateClientProfileLink($note->getClient())
        ];
    }

    /**
     * Confirm delete user form
     *
     * @Route("{noteId}/delete", name="delete_note")
     * @Template("AppBundle:Pa/ClientProfile:deleteConfirm.html.twig")
     */
    public function deleteConfirmAction(Request $request, $noteId, $confirmed = false)
    {
        /** @var EntityDir\Note $note */
        $note = $this->getNote($noteId);

        $this->denyAccessUnlessGranted('delete-note', $note, 'Access denied');

        return [
            'report'  => $note->getClient()->getCurrentReport(),
            'note' => $note,
            'client' => $note->getClient(),
            'backLink' => $this->generateClientProfileLink($note->getClient())
        ];
    }

    /**
     * Removes a note, adds a flash message and redirects to page
     *
     * @Route("{noteId}/delete/confirm", name="delete_note_confirm")
     * @Template()
     */
    public function deleteConfirmedAction(Request $request, $noteId)
    {
        try {
            /** @var EntityDir\Note $note */
            $note = $this->getNote($noteId);

            $this->denyAccessUnlessGranted('delete-note', $note, 'Access denied');

            $this->getRestClient()->delete('note/' . $noteId);

            $request->getSession()->getFlashBag()->add('notice', 'Note has been removed');
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());

            $request->getSession()->getFlashBag()->add(
                'error',
                'Note could not be removed'
            );
        }

        return $this->redirect($this->generateClientProfileLink($note->getClient()));
    }

    /**
     * Retrieves the note object with required associated entities to populate the table and back links
     *
     * @param $noteId
     * @return mixed
     */
    private function getNote($noteId)
    {
        return $this->getRestClient()->get(
            'note/' . $noteId,
            'Note',
            ['notes', 'client', 'current-report', 'report-id', 'note-client', 'user']
        );
    }
}

<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);

        $report = $client->getCurrentReport();

        $note = new EntityDir\Note($client);

        $returnLink = $this->generateUrl('report_overview', ['reportId' => $report->getId()]);

        $form = $this->createForm(
            new FormDir\Pa\NoteType($this->get('translator')),
            $note
        );


        $form->handleRequest($request);

        if ($form->isValid()) {
            $note = $form->getData();

            $this->getRestClient()->post('report/' . $client->getId() . '/note', $note, ['add_note']);
            $request->getSession()->getFlashBag()->add('notice', 'The note has been added');

            return $this->redirect($returnLink);
        }

        return [
            'form'  => $form->createView(),
            'client' => $client,
            'report' => $report,
            'backLink' => $returnLink
        ];
    }

    /**
     * @Route("{noteId}/edit", name="edit_note")
     * @Template("AppBundle:Pa/ClientProfile:editNote.html.twig")
     */
    public function editAction(Request $request, $noteId)
    {
        $note = $this->getRestClient()->get('note/' . $noteId, 'Note', ['notes', 'user']); /* @var $note EntityDir\Note*/
        // hack check
        if ($note->getCreatedBy()->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Cannot edit notes created by others');
        }

        //TODO remove when client is used instead of report
        $report = $this->getRestClient()->get("report/".$request->get('reportId'), 'Report\\Report', ['report-id', 'client', 'report-106-flag']);
        $returnLink = $this->generateUrl('report_overview', ['reportId' => $report->getId()]);

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

            return $this->redirect($returnLink);
        }


        return [
            'report'  => $report,
            'form'  => $form->createView(),
            'backLink' => $returnLink
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
        $note = $this->getRestClient()->get('note/' . $noteId, 'Note', ['notes', 'current-report', 'report-id', 'report-client', 'client', 'user']);

        $this->denyAccessUnlessGranted('delete-note', $note, 'Access denied');

        return [
            'note' => $note,
            'client' => $note->getClient(),
            'backLink' => $this->generateReturnLink($note)
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
            $note = $this->getRestClient()->get(
                'note/' . $noteId,
                'Note',
                ['notes', 'current-report', 'report-id', 'report-client']
            );
            $this->denyAccessUnlessGranted('delete-note', $note, 'Access denied');

            $this->getRestClient()->delete('note/' . $noteId, $note);

            $request->getSession()->getFlashBag()->add('notice', 'Note has been removed');

        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());

            $request->getSession()->getFlashBag()->add(
                'error',
                'Note could not be removed'
            );
        }

        return $this->redirect($this->generateReturnLink($note));
    }

    /**
     * Generate back to overview page of current client report
     *
     * @param EntityDir\Note $note
     * @return string
     */
    private function generateReturnLink(EntityDir\Note $note)
    {
        // generate return link
        $report = $note->getClient()->getCurrentReport();

        return $this->generateUrl('report_overview', ['reportId' => $report->getId()]);
    }
}


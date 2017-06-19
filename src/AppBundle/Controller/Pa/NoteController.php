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
}

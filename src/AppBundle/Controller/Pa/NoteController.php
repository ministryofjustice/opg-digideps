<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class NoteController extends AbstractController
{
    private static $jmsGroups = [
        'notes'
    ];

    /**
     * @Route("/note/add", name="add_note")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $clientId = $request->get('clientId');

        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);

        $report = $client->getCurrentReport();

        $note = new EntityDir\Note($client);
        $template = 'AppBundle:Pa/ClientProfile:addNote.html.twig';

        $form = $this->createForm(
            new FormDir\Pa\NoteType(
                $this->get('translator'),
                $note
            ),
            $note
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $note = $form->getData();

            $this->getRestClient()->post('report/' . $report->getId() . '/note', $note, ['add_note']);
            $request->getSession()->getFlashBag()->add('notice', 'The note has been added');

            return $this->redirectToRoute('report_overview', ['reportId' => $report->getId()]);
        }

        return $this->render($template, [
            'form'  => $form->createView(),
            'client' => $client,
            'report' => $report,
        ]);
    }

    /**
     * @Route("/note/{noteId}/edit", name="edit_note")
     * @Template("AppBundle:Pa/ClientProfile:editNote.html.twig")
     */
    public function editAction(Request $request, $noteId)
    {
        $note = $this->getRestClient()->get('note/' . $noteId, 'Note'); /* @var $note EntityDir\Note*/
        // hack check
        if ($note->getCreatedBy()->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Cannot edit notes creaed by others');
        }

        //TMP: remove when the new client profile page uses clientId
        $report = $this->getReportIfNotSubmitted($request->get('reportId'), self::$jmsGroups);

        $form = $this->createForm(
            new FormDir\Pa\NoteType(
                $this->get('translator'),
                $note
            ),
            $note
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $note = $form->getData();

            $this->getRestClient()->put('note/' . $noteId, $note, ['add_note']);
            $request->getSession()->getFlashBag()->add('info', 'The note has been added');

            $request->getSession()->getFlashBag()->add(
                'notice',
                'The note has been edited'
            );

            return $this->redirectToRoute('report_overview', ['reportId'=>$report->getId()]);
        }

        return [
            'report'  => $report,
            'form'  => $form->createView(),
        ];
    }
}

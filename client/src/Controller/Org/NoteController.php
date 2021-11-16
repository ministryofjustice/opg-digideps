<?php

namespace App\Controller\Org;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\RestClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/note/")
 */
class NoteController extends AbstractController
{
    public function __construct(private ClientApi $clientApi, private RestClient $restClient)
    {
    }

    /**
     * @Route("add", name="add_note")
     * @Template("@App/Org/ClientProfile/addNote.html.twig")
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $clientId = $request->get('clientId');

        /** @var $client EntityDir\Client */
        $client = $this->restClient->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report', 'client-users', 'user']);

        $this->denyAccessUnlessGranted('add-note', $client, 'Access denied');

        $report = $client->getCurrentReport();
        $report->setClient($client);

        $note = new EntityDir\Note($client);

        $form = $this->createForm(
            FormDir\Org\NoteType::class,
            $note
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();

            $this->restClient->post('note/' . $client->getId(), $note, ['add_note']);
            $request->getSession()->getFlashBag()->add('notice', 'The note has been added');

            return $this->redirect($this->clientApi->generateClientProfileLink($note->getClient()));
        }

        return [
            'form'  => $form->createView(),
            'client' => $client,
            'report' => $report,
            'backLink' => $this->clientApi->generateClientProfileLink($note->getClient())
        ];
    }

    /**
     * @Route("{noteId}/edit", name="edit_note")
     * @Template("@App/Org/ClientProfile/editNote.html.twig")
     * @throws \Exception
     */
    public function editAction(Request $request, $noteId)
    {
        /** @var EntityDir\Note $note */
        $note = $this->getNote($noteId);

        $this->denyAccessUnlessGranted('edit-note', $note, 'Access denied');

        $form = $this->createForm(
            FormDir\Org\NoteType::class,
            $note
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $form->getData();

            $this->restClient->put('note/' . $noteId, $note, ['add_note']);
            $request->getSession()->getFlashBag()->add(
                'notice',
                'The note has been edited'
            );

            return $this->redirect($this->clientApi->generateClientProfileLink($note->getClient()));
        }

        return [
            'report'  => $note->getClient()->getCurrentReport()->setClient($note->getClient()),
            'form'  => $form->createView(),
            'client' => $note->getClient(),
            'backLink' => $this->clientApi->generateClientProfileLink($note->getClient())
        ];
    }

    /**
     * Confirm delete user form
     *
     * @Route("{noteId}/delete", name="delete_note")
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @param $noteId
     * @throws \Exception
     */
    public function deleteConfirmAction(Request $request, $noteId, LoggerInterface $logger): array|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        /** @var EntityDir\Note $note */
        $note = $this->getNote($noteId);

        $this->denyAccessUnlessGranted('delete-note', $note, 'Access denied');

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var EntityDir\Note $note */
                $note = $this->getNote($noteId);

                $this->denyAccessUnlessGranted('delete-note', $note, 'Access denied');

                $this->restClient->delete('note/' . $noteId);

                $request->getSession()->getFlashBag()->add('notice', 'Note has been removed');
            } catch (\Throwable $e) {
                $logger->error($e->getMessage());

                $request->getSession()->getFlashBag()->add('error', 'Note could not be removed');
            }

            return $this->redirect($this->clientApi->generateClientProfileLink($note->getClient()));
        }

        return [
            'translationDomain' => 'client-notes',
            'report' => $note->getClient()->getCurrentReport(),
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.title', 'value' => $note->getTitle()],
                ['label' => 'deletePage.summary.createdOn', 'value' => $note->getCreatedOn(), 'format' => 'date'],
            ],
            'backLink' => $this->clientApi->generateClientProfileLink($note->getClient()),
        ];
    }

    /**
     * Retrieves the note object with required associated entities to populate the table and back links
     *
     * @param $noteId
     * @return mixed
     */
    private function getNote($noteId)
    {
        return $this->restClient->get(
            'note/' . $noteId,
            'Note',
            ['notes', 'client', 'client-users', 'current-report', 'report-id', 'note-client', 'user']
        );
    }
}

<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Note;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route(path: '/note/')]
class NoteController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '{clientId}', requirements: ['clientId' => '\d+'], methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ORG')]
    public function add(Request $request, int $clientId): array
    {
        $client = $this->findEntityBy(Client::class, $clientId); /* @var $report \App\Entity\Client */
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        // hydrate and persist
        $data = $this->formatter->deserializeBodyContent($request, [
            'title' => 'notEmpty',
            'category' => 'mustExist',
            'content' => 'mustExist',
        ]);
        $note = new Note($client, $data['category'], $data['title'], $data['content']);
        $note->setCreatedBy($this->getUser());

        $this->em->persist($note);
        $this->em->flush();

        return ['id' => $note->getId()];
    }

    /**
     * GET note by id.
     *
     * User that created the note is not returned as default, as not currently needed from the CLIENT.
     * Add "user" group if needed
     */
    #[Route(path: '{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ORG')]
    public function getOneById(Request $request, int $id): Note
    {
        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['notes', 'user'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $note = $this->findEntityBy(Note::class, $id); /* @var $note \App\Entity\Note */
        $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());

        return $note;
    }

    /**
     * Update note
     * Only the creator can update the note.
     */
    #[Route(path: '{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_ORG')]
    public function updateNote(Request $request, int $id): int
    {
        $note = $this->findEntityBy(Note::class, $id); /* @var $note \App\Entity\Note */

        // enable if the check above is removed and the note is available for editing for the whole team
        $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->hydrateEntityWithArrayData($note, $data, [
            'category' => 'setCategory',
            'title' => 'setTitle',
            'content' => 'setContent',
        ]);

        $note->setLastModifiedBy($this->getUser());

        $this->em->flush($note);

        return $note->getId();
    }

    #[Route(path: '{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_ORG')]
    public function delete(int $id, LoggerInterface $logger): array
    {
        try {
            $note = $this->findEntityBy(Note::class, $id);

            // enable if the check above is removed and the note is available for editing for the whole team
            $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());

            $this->em->remove($note);

            $this->em->flush($note);
        } catch (Throwable $e) {
            $logger->error('Failed to delete note ID: '.$id.' - '.$e->getMessage());
        }

        return [];
    }
}

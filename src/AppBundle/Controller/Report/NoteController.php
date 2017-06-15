<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class NoteController extends RestController
{
    /**
     * @Route("/report/{reportId}/note", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $data = $this->deserializeBodyContent($request, ['title' => 'mustExist']);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $note = new EntityDir\Note($report->getClient(), $data['category'], $data['title'], $data['content']);

        $note->setCreatedBy($this->getUser());

        $this->persistAndFlush($note);

        return ['id' => $note->getId()];
    }

    /**
     * @Route("/note/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['notes'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $note = $this->findEntityBy(EntityDir\Note::class, $id); /* @var $note EntityDir\Note */
        $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());

        return $note;
    }

    /**
     * @Route("/note/{id}")
     * @Method({"PUT"})
     */
    public function updateNote(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $note = $this->findEntityBy(EntityDir\Note::class, $id); /* @var $note EntityDir\Note */
        $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());

        $data = $this->deserializeBodyContent($request);
        $this->hydrateEntityWithArrayData($note, $data, [
            'category' => 'setCategory',
            'title' => 'setTitle',
            'content' => 'setContent',
        ]);

        $this->getEntityManager()->flush($note);

        return $note;
    }
}

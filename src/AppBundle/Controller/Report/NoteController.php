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

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->validateArray($data, [
            'title' => 'mustExist',
        ]);
        $note = new EntityDir\Note($report, $data['category'], $data['title'], $data['content']);

        $note->setCreatedBy($this->getUser());

        $this->persistAndFlush($note);

        return ['id' => $note->getId()];
    }
}

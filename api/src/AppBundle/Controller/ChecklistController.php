<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Repository\ChecklistRepository;
use AppBundle\Exception\UnauthorisedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChecklistController extends RestController
{
    /**
     * @Route("/checklist/queued", methods={"GET"})
     * @return string
     */
    public function getQueuedChecklists(Request $request, EntityManagerInterface $em): string
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        /** @var array $data */
        $data = $this->deserializeBodyContent($request);

        /** @var ChecklistRepository $checklistRepo */
        $checklistRepo = $em->getRepository(Checklist::class);

        return json_encode($checklistRepo->getQueuedAndSetToInProgress(intval($data['row_limit'])));
    }
}

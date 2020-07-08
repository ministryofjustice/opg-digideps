<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ChecklistRepository;
use AppBundle\Exception\UnauthorisedException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/checklist")
 */
class ChecklistController extends RestController
{
    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, int $id, EntityManagerInterface $em): Checklist
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        /** @var array $data */
        $data = $this->deserializeBodyContent($request);

        /** @var Checklist $checklist */
        $checklist = $em->getRepository(Checklist::class)->find($id);

        if (!empty($data['syncStatus'])) {
            $checklist->setSynchronisationStatus($data['syncStatus']);

            if ($data['syncStatus'] == Checklist::SYNC_STATUS_PERMANENT_ERROR) {
                $errorMessage = is_array($data['syncError']) ? json_encode($data['syncError']) : $data['syncError'];
                $checklist->setSynchronisationError($errorMessage);
            } else {
                $checklist->setSynchronisationError(null);
            }

            if ($data['syncStatus'] == Checklist::SYNC_STATUS_SUCCESS) {
                $checklist->setSynchronisationTime(new DateTime());
            }
        }

        if (!empty($data['uuid'])) {
            $checklist->setUuid($data['uuid']);
        }

        $this->persistAndFlush($checklist);

        $serialisedGroups = ['checklist-id'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $checklist;
    }
}

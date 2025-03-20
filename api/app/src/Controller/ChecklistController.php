<?php

namespace App\Controller;

use App\Entity\Report\Checklist;
use App\Exception\UnauthorisedException;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/checklist")
 */
class ChecklistController extends RestController
{
    public function __construct(private readonly AuthService $authService, private readonly RestFormatter $formatter)
    {
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, int $id, EntityManagerInterface $em): Checklist
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        /** @var array $data */
        $data = $this->formatter->deserializeBodyContent($request);

        /** @var Checklist $checklist */
        $checklist = $em->getRepository(Checklist::class)->find($id);

        if (!empty($data['syncStatus'])) {
            $checklist->setSynchronisationStatus($data['syncStatus']);

            if (Checklist::SYNC_STATUS_PERMANENT_ERROR == $data['syncStatus']) {
                $errorMessage = is_array($data['syncError']) ? json_encode($data['syncError']) : $data['syncError'];
                $checklist->setSynchronisationError($errorMessage);
            } else {
                $checklist->setSynchronisationError(null);
            }

            if (Checklist::SYNC_STATUS_SUCCESS == $data['syncStatus']) {
                $checklist->setSynchronisationTime(new \DateTime());
            }
        }

        if (!empty($data['uuid'])) {
            $checklist->setUuid($data['uuid']);
        }

        $em->persist($checklist);
        $em->flush();

        $serialisedGroups = ['synchronisation', 'checklist-id', 'checklist-uuid'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $checklist;
    }
}

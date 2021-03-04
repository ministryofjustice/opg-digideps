<?php

namespace App\Controller;

use App\Entity\Report\Checklist;
use App\Repository\ChecklistRepository;
use App\Exception\UnauthorisedException;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/checklist")
 */
class ChecklistController extends RestController
{
    private AuthService $authService;
    private RestFormatter $formatter;

    public function __construct(AuthService $authService, RestFormatter $formatter)
    {
        $this->authService = $authService;
        $this->formatter = $formatter;
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

        $em->persist($checklist);
        $em->flush();

        $serialisedGroups = ['synchronisation', 'checklist-id', 'checklist-uuid'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $checklist;
    }
}

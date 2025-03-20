<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\UserResearchResponseFactory;
use App\Repository\SatisfactionRepository;
use App\Repository\UserResearchResponseRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserResearchController extends RestController
{
    public function __construct(
        private readonly UserResearchResponseFactory $factory,
        private readonly UserResearchResponseRepository $userResearchResponseRepository,
        private readonly SatisfactionRepository $satisfactionRepository,
        private readonly RestFormatter $formatter,
        EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    /**
     * @Route("/user-research", name="create_user_research", methods={"POST"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ORG')")
     */
    public function create(Request $request)
    {
        try {
            $formData = json_decode($request->getContent(), true);

            $satisfactionId = $formData['satisfaction'] ?? null;
            $formData['satisfaction'] = $satisfactionId ? $this->satisfactionRepository->find($satisfactionId) : null;

            $userResearchResponse = $this->factory->generateFromFormData($formData);
            $this->userResearchResponseRepository->create($userResearchResponse, $this->getUser());

            $groups = $request->get('groups') ? $request->get('groups') : ['satisfaction', 'user-research', 'user'];
            $this->formatter->setJmsSerialiserGroups($groups);

            return 'Created';
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('UserResearchResponse not created: %s', $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/user-research", name="get_user_research", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getAll(Request $request)
    {
        try {
            $fromDateString = $request->get('fromDate', '');
            $fromDate = empty($fromDateString) ?
                (new \DateTime('-5 years'))->setTime(0, 0, 1) : (new \DateTime($fromDateString))->setTime(0, 0, 1);

            $toDateString = $request->get('toDate', '');
            $toDate = empty($toDateString) ?
                (new \DateTime())->setTime(23, 59, 59) : (new \DateTime($toDateString))->setTime(23, 59, 59);

            $groups = ['satisfaction', 'user-research', 'user-email', 'user-phone-main'];
            $this->formatter->setJmsSerialiserGroups($groups);

            return new JsonResponse(['data' => $this->userResearchResponseRepository->getAllFilteredByDate($fromDate, $toDate), 'success' => true]);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('There was a problem getting user research responses: %s', $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }
}

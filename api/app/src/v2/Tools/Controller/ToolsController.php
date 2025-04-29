<?php

declare(strict_types=1);

namespace App\v2\Tools\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\v2\Controller\ControllerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/tools')]
class ToolsController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private ReportRepository $reportRepository,
        private UserRepository $userRepository,
        private ClientRepository $clientRepository,
    ) {
    }

    /**
     * @return JsonResponse
     *
     * @throws \Exception
     */
    #[Route(path: '/reassign-reports', methods: ['POST'])]
    #[Security("is_granted('ROLE_SUPER_ADMIN')")]
    public function reassignReports(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $firstClientId = intval($fromRequest['firstClientId']);
        $secondClientId = intval($fromRequest['secondClientId']);

        if (0 == $firstClientId || 0 == $secondClientId) {
            return $this->buildErrorResponse('The client ids provided are not valid numbers!');
        }

        if ($firstClientId == $secondClientId) {
            return $this->buildErrorResponse('The client ids provided are the same!');
        }

        /** @var Client $firstClient */
        if (null === $firstClient = $this->clientRepository->findByIdIncludingDischarged($firstClientId)) {
            return $this->buildErrorResponse(sprintf('First Client with id %s not found', $firstClientId));
        }

        /** @var Client $secondClient */
        if (null === $secondClient = $this->clientRepository->findByIdIncludingDischarged($secondClientId)) {
            return $this->buildErrorResponse(sprintf('Second Client with id %s not found', $secondClientId));
        }

        if ($firstClient->getCaseNumber() != $secondClient->getCaseNumber()) {
            return $this->buildErrorResponse('The clients have two different case numbers!');
        }

        $firstReports = $firstClient->getReports();
        $secondReports = $secondClient->getReports();

        foreach ($firstReports as $report) {
            $report->setClient($secondClient);
        }

        foreach ($secondReports as $report) {
            $report->setClient($firstClient);
        }

        $this->em->flush();

        return $this->buildSuccessResponse([], 'Reports have been reassigned successfully');
    }
}

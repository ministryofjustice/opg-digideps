<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller\Admin;

use OPG\Digideps\Backend\Cleanup\ReportCleaner;
use OPG\Digideps\Backend\Exception\NotFound;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Common\Cleanup\CleanupModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: 'admin/cleanup')]
class CleanupController extends AbstractController
{
    public function __construct(
        private readonly ReportCleaner $reportCleaner,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    #[Route(path: '/clients/reports', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function reportCleanup(Request $request): Response
    {
        try {
            $model = unserialize($request->getContent(), ['allowed_classes' => [CleanupModel::class], 'max_depth' => 1]);
            if (!$model instanceof CleanupModel) {
                throw new \TypeError();
            }
            $clientIds = [];
            if ($model->caseNumber !== null) {
                array_push($clientIds, ...array_map(fn (string $caseNumber): int => $this->clientRepository->findByCaseNumber($caseNumber)?->getId() ?? throw new NotFound("Client with case number {$caseNumber}"), explode(',', $model->caseNumber)));
            }
            return new Response($this->reportCleaner->clean(!$model->notDryRun, $model->allowNotContinuous, ...$clientIds), 200);
        } catch (\Throwable $throwable) {
            return new Response(str_replace("\n", "\n\r", $throwable->getMessage() . ":\n" . $throwable->getTraceAsString()), 500);
        }
    }
}

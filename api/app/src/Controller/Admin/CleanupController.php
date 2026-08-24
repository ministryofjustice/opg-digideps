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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/cleanup/')]
class CleanupController extends AbstractController
{
    public function __construct(
        private readonly ReportCleaner $reportCleaner,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    #[Route(path: 'reports/download/{kind}', name: 'admin_cleanup_reports_download', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function reportGenerateActions(string $kind): Response
    {
        return match ($kind) {
            'actions' => new Response($this->reportCleaner->generateActionReport(), 200),
            'problems' => new Response($this->reportCleaner->generateProblemReport(), 200),
            default => new Response(status: 404)
        };
    }

    #[Route(path: 'reports/plan', methods: ['POST'])]
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
            if (!empty($clientIds)) {
                $this->reportCleaner->clean($model->allowNotContinuous, ...$clientIds);
            }
            if ($model->notDryRun) {
                $count = $this->reportCleaner->executeActions();
                return new Response(json_encode(['ok' => true, 'count' => $count]), 200);
            }
            return new Response(json_encode(['ok' => true]), 200);
        } catch (\Throwable $throwable) {
            return new Response(json_encode(['ok' => false, 'message' => str_replace("\n", "\n\r", $throwable->getMessage() . ":\n" . $throwable->getTraceAsString())]), 200);
        }
    }
}

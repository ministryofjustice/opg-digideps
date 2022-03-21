<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\ChecklistSyncService;
use App\Service\Client\Internal\ReportApi;
use App\Service\ParameterStoreService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    public function __construct(
        private KernelInterface $kernel,
        private string $symfonyEnvironment,
        private ChecklistSyncService $checklistSyncService,
        private ReportApi $reportApi,
        private ParameterStoreService $parameterStore
    ) {
    }

    /**
     * @Route("/admin/behat/run-document-sync-command", name="behat_admin_run_document_sync_command", methods={"GET"})
     *
     * @param KernelInterface $kernel
     *
     * @return Response
     *
     * @throws Exception
     */
    public function runDocumentSyncCommand()
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'digideps:document-sync']);
        $output = new NullOutput();

        $application->run($input, $output);

        return new Response('');
    }

    /**
     * @Route("/admin/behat/run-checklist-sync-command", name="behat_admin_run_checklist_sync_command", methods={"GET"})
     *
     * @param KernelInterface $kernel
     *
     * @return Response
     *
     * @throws Exception
     */
    public function runChecklistSyncCommand()
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT) ?: '30';
        $reports = $this->reportApi->getReportsWithQueuedChecklists($limit);
        $notProcessedCount = $this->checklistSyncService->processChecklistsInCommand($reports);

        $message = $notProcessedCount > 0 ? sprintf('%s checklists failed to sync', $notProcessedCount) : 'Sync completed';
        $statusCode = $notProcessedCount > 0 ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK;

        return new Response($message, $statusCode);
    }
}

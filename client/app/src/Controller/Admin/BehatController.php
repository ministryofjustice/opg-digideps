<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Command\ChecklistSyncCommand;
use App\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    public function __construct(
        private KernelInterface $kernel,
        private string $symfonyEnvironment,
    ) {
    }

    /**
     * @Route("/admin/behat/run-document-sync-command", methods={"GET"}, name="behat_admin_run_document_sync_command")
     *
     * @throws \Exception
     */
    public function runDocumentSyncCommand(): Response
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
     * @Route("/admin/behat/run-checklist-sync-command", methods={"GET"}, name="behat_admin_run_checklist_sync_command")
     *
     * @throws \Exception
     */
    public function runChecklistSyncCommand(): Response
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'digideps:checklist-sync']);
        $output = new BufferedOutput();

        $application->run($input, $output);

        $timeOut = 10;
        $startTime = time();

        while (true) {
            if ((time() - $startTime) > $timeOut) {
                return new Response('Command timed out', Response::HTTP_REQUEST_TIMEOUT);
            }

            if (str_contains($output->fetch(), ChecklistSyncCommand::COMPLETED_MESSAGE)) {
                return new Response('');
            }

            sleep(1);
        }
    }
}

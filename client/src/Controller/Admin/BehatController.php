<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    private KernelInterface $kernel;
    private RestClient $restClient;
    private string $symfonyEnvironment;

    public function __construct(KernelInterface $kernel, RestClient $restClient, string $symfonyEnvironment)
    {
        $this->kernel = $kernel;
        $this->restClient = $restClient;
        $this->symfonyEnvironment = $symfonyEnvironment;
    }

    /**
     * @Route("/admin/behat/run-document-sync-command", name="behat_admin_run_sync_command", methods={"GET"})
     *
     * @param KernelInterface $kernel
     * @return Response
     * @throws \Exception
     */
    public function runDocumentSyncCommand()
    {
        if ($this->symfonyEnvironment === 'prod') {
            throw $this->createNotFoundException();
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'digideps:document-sync']);
        $output = new NullOutput();

        $application->run($input, $output);

        return new Response('');
    }
}

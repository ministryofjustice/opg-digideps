<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    private KernelInterface $kernel;
    private RestClient $restClient;

    public function __construct(KernelInterface $kernel, RestClient $restClient)
    {
        $this->kernel = $kernel;
        $this->restClient = $restClient;
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
        if ($this->kernel->getEnvironment() === 'prod') {
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
     * @Route("/admin/behat/reset-fixtures", name="behat_admin_reset_fixtures", methods={"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function resetFixtures()
    {
        try {
            $this->restClient->get(
                '/v2/fixture/reset-fixtures',
                'raw'
            );

            return new Response('Behat fixtures loaded');
        } catch (\Throwable $e) {
            return new Response(
                sprintf('Behat fixtures not loaded: %s', $e->getMessage()),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

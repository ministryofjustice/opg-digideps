<?php declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class BehatController extends AbstractController
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Route("/admin/run-document-sync-command")
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
     * @Route("/admin/navigate-to/{$routeName}")
     *
     * @param KernelInterface $router
     * @return Response
     * @throws \Exception
     */
    public function navigateToRouteName(Request $request, RouterInterface $router, string $routeName)
    {
        if ($this->kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $params = $request->request->all();
        $url = $router->generate($routeName, $params);

        $router->handle();

        return new Response('');
    }
}

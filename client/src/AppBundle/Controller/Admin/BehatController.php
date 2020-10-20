<?php declare(strict_types=1);

namespace AppBundle\Controller\Admin;


use AppBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    /**
     * @Route("/admin/run-document-sync-command")
     *
     * @param KernelInterface $kernel
     * @return Response
     * @throws \Exception
     */
    public function runDocumentSyncCommand(KernelInterface $kernel)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'digideps:document-sync']);
        $output = new NullOutput();

        $application->run($input, $output);

        return new Response('');
    }
}

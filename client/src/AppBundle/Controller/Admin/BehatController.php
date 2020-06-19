<?php declare(strict_types=1);

namespace AppBundle\Controller\Admin;


use AppBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    /**
     * @Route("/admin/run-cache-clear-command")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function runCacheClearCommand(KernelInterface $kernel)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'cache:clear']);
        $output = new NullOutput();

        $application->run($input, $output);

        return new Response('');
    }
}

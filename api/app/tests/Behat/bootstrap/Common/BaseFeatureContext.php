<?php

namespace App\Tests\Behat\Common;

use Behat\MinkExtension\Context\MinkContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseFeatureContext extends MinkContext
{
    use AuthenticationTrait;
    use DebugTrait;
    use FormTrait;
    use SiteNavigationTrait;

    protected static $dbName = 'api';

    protected Application $application;
    public BufferedOutput $output;

    public function __construct(
        protected readonly KernelInterface $kernel
    ) {   // Required so we can run tests against commands
        $this->application = new Application($kernel);
        $this->application->setCatchExceptions(true);
        $this->application->setAutoExit(true);
        $this->output = new BufferedOutput();
    }

    public function getAdminUrl(): string
    {
        return getenv('ADMIN_HOST');
    }
}

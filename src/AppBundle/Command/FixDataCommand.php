<?php

namespace AppBundle\Command;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add data that wasn't added with listeners
 * Firstly wrote when data wasn't added with temporary 103 user on staging
 *
 * @codeCoverageIgnore
 */
class FixDataCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fix-data')
            ->setDescription('add missing data to reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');

        $fixDataService = new FixDataService($em);
        $messages = $fixDataService->fixReports()->fixNdrs()->getMessages();
        foreach($messages as $m) {
            $output->writeln($m);
        }
        $output->writeln('Done');
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserRetentionPolicyCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('digideps:user-retention-policy')
            ->setDescription('Deletes inactive admin user accounts')
        ;
    }

      protected function execute(InputInterface $input, OutputInterface $output): string
      {
          dd('Hello World');
      }
}

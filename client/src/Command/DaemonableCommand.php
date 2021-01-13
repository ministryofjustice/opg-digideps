<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class DaemonableCommand extends Command
{
    /** @var bool */
    private $shutdownRequested = false;

    protected function configure(): void
    {
        $this
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Whether to run in daemon mode');
        ;
    }

    protected function daemonize(InputInterface $input, OutputInterface $output, callable $callback, int $interval): int
    {
        if ($input->getOption('daemon')) {
            $stopCommand = function () use ($output) {
                $output->writeln('Stopping...');
                $this->shutdownRequested = true;
            };

            pcntl_signal(SIGTERM, $stopCommand);
            pcntl_signal(SIGINT, $stopCommand);
        } else {
            $this->shutdownRequested = true;
        }

        do {
            call_user_func($callback, $input, $output);

            pcntl_signal_dispatch();

            if (!$this->shutdownRequested) {
                sleep($interval);
            }
        } while (!$this->shutdownRequested);

        return 0;
    }
}

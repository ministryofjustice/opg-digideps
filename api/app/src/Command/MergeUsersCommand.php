<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\UserMergeResult;
use App\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Command to merge one user account into another.
 */
class MergeUsersCommand extends Command
{
    use ContainerAwareTrait;

    public function __construct(
        private readonly UserService $userService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('digideps:merge-users')
            ->setDescription('Merge two users together')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'Email of the user to be merged from, into the "to" user; the "from" user will be made inactive'
            )
            ->addArgument(
                'into',
                InputArgument::REQUIRED,
                'Email of user to merge the "from" user into; this user will be kept and will remain active'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromEmail = $input->getArgument('from');
        $intoEmail = $input->getArgument('into');

        $result = $this->userService->mergeUsers($fromEmail, $intoEmail);

        if (UserMergeResult::MERGED !== $result) {
            $output->writeln('ERROR: '.$result->value);

            return 1;
        }

        $output->writeln($result->value."; primary account is now $intoEmail");

        return 0;
    }
}

<?php

namespace App\Command;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Used to compile a list of deleted files
 *
 * To edit on admin box
 * vi /app/src/App/Command/DocumentRecoverCommand.php
 *
 * Run on admin container
 * /sbin/setuser app php app/console digideps:recover-documents <pathToFileWithOneRefPerLine>
 *
 */
class DocumentRecoverCommand extends Command
{
    public function __construct(private S3Client $s3, private string $s3BucketName)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('digideps:recover-documents')
            ->addArgument('file', InputArgument::OPTIONAL)
            ->addOption('file', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $refs = array_map('trim', array_filter(file($input->getArgument('file'))));

        $ok = 0;
        $denied = 0;

        foreach ($refs as $ref) {
            try {
                $this->s3->getObject([
                    'Bucket' => $this->s3BucketName,
                    'Key'    => $ref
                ]);
                $ok++;
            } catch (S3Exception $e) {
                $output->writeln($e->getMessage());
                $denied++;
            }
        }

        $output->writeln("ok $ok - denied $denied");
    }
}

<?php declare(strict_types=1);


namespace AppBundle\Command;


use AppBundle\Entity\Repository\OrganisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class UpdateOrgNames extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var OrganisationRepository
     */
    private $repository;

    /**
     * CsvImportCommand constructor.
     *
     * @param EntityManagerInterface $em
     * @param OrganisationRepository $repository
     */
    public function __construct(EntityManagerInterface $em, OrganisationRepository $repository)
    {
        parent::__construct();

        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * Configure
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('orgs:update:names')
            ->setDescription('Updates the name of an org based on a CSV with email identifier and org name')
            ->addArgument('CSVName', InputArgument::REQUIRED, 'The name of the CSV')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $fileName = $input->getArgument('CSVName');
        $fileLocation = $this->getFileLocation($fileName);

        if (empty($fileLocation)) {
            $io->error("Could not find file with name $fileName");
            return;
        }

        $csv = Reader::createFromPath($fileLocation);
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);

        $success = 0;
        $failure = 0;

        foreach ($records as $row) {
            $org = $this->repository->findByEmailIdentifier($row['email_identifier']);

            if ($org !== null) {
                $org->setName($row['organisation_name']);
                $this->em->persist($org);
                ++$success;
            } else {
                ++$failure;
            }
        }

        $this->em->flush();

        $io->success("Successfully updated $success, failed to update $failure");
    }

    /**
     * @param string $fileName
     * @return false|string
     */
    private function getFileLocation(string $fileName)
    {
        $finder = new Finder();
        $finder->files()->in('*')->name($fileName);
        $absoluteFilePath = '';

        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();
        }

        return $absoluteFilePath;
    }

}

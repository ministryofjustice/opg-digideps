<?php

namespace AppBundle\Command;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Service\CasrecVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add data that wasn't added with listeners
 * Firstly wrote when data wasn't added with temporary 103 user on staging
 *
 * @codeCoverageIgnore
 */
class CleanDataCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    /** @var EntityManagerInterface */
    private $em;

    /** @var CasrecVerificationService */
    private $verificationService;

    public function __construct(EntityManagerInterface $em, CasrecVerificationService $verificationService)
    {
        $this->em = $em;
        $this->verificationService = $verificationService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('digideps:clean-data')
            ->setDescription('delete unassigned and duplicate reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         *
         * add DeputyNo to LAY users that skipped the self-registration process.
         * DeputyNo is needed for stats
         *
         */
        /** @var $user User */
        $fixed = 0;
        $mismatch = 0;
        $clientNotCreated = 0;

        foreach ($this->em->getRepository(User::class)->findBy([
            'deputyNo'=>null,
            'roleName' => User::ROLE_LAY_DEPUTY,
        ]) as $user) {
            $output->write("User {$user->getId()}: ");
            try {
                $client = $user->getFirstClient();
                if (!$client) {
                    $clientNotCreated++;
                    throw new \Exception('Client not yet created. skipped');
                }

                $caseNumber = $client->getCaseNumber();
                $clientSurname = $client->getLastname();
                $deputySurname = $user->getLastname();
                $deputyPostcode = $user->getAddressPostcode();

                $this->verificationService->validate($caseNumber, $clientSurname, $deputySurname, $deputyPostcode);
                $deputyNo = implode(',', $this->verificationService->getLastMatchedDeputyNumbers());
                $user->setDeputyNo($deputyNo);
                $this->em->flush($user);
                $output->writeln(" deputyNo set to $deputyNo ");
                $fixed++;
            } catch (\Throwable $e) {
                $error = $e->getCode() === 400 ? 'CASREC match not found' : $e->getMessage();
                $output->writeln(' ERROR: ' . $error);
                $mismatch++;
            }
        }

        $output->writeln("Done. Fixed $fixed , mismatch  $mismatch , client not created $clientNotCreated");
    }
}

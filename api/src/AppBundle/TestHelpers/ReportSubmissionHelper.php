<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReportSubmissionHelper extends KernelTestCase
{
    /**
     * @param EntityManager $em
     * @return ReportSubmission
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateAndPersistReportSubmission(EntityManager $em)
    {
        $client = new Client();
        $report = (new ReportTestHelper())->generateReport($client);
        $user = (new User)
            ->setFirstname('First')
            ->setLastname('Last')
            ->setEmail(sprintf('first.last.%d@example.com', mt_rand(1, 999999)))
            ->setRoleName(User::ROLE_ADMIN);

        $reportSubmission = new ReportSubmission($report, $user);

        $em->persist($client);
        $em->persist($report);
        $em->persist($user);
        $em->persist($reportSubmission);
        $em->flush();

        return $reportSubmission;
    }
}

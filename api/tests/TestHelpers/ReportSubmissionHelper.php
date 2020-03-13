<?php declare(strict_types=1);


namespace Tests\TestHelpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReportSubmissionHelper extends KernelTestCase
{
    /**
     * @return ReportSubmission
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateAndPersistReportSubmission()
    {
        $client = new Client();
        $report = (new ReportTestHelper())->generateReport($client);
        $user = (new User)
            ->setFirstname('First')
            ->setLastname('Last')
            ->setEmail('first.last@example.com')
            ->setRoleName(User::ROLE_ADMIN);
        // create a reportSubmission
        $reportSubmission = new ReportSubmission($report, $user);

        $em = (self::bootKernel(['debug' => false]))->getContainer()->get('em');
        $em->persist($client);
        $em->persist($report);
        $em->persist($user);
        $em->persist($reportSubmission);
        $em->flush();

        return $reportSubmission;
    }
}

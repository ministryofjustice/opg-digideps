<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
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
        $faker = Factory::create();

        $client = new Client();
        $report = (new ReportTestHelper())->generateReport($client);
        $user = (new User)
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setEmail($faker->safeEmail)
            ->setRoleName(User::ROLE_LAY_DEPUTY);

        $reportSubmission = new ReportSubmission($report, $user);

        $em->persist($client);
        $em->persist($report);
        $em->persist($user);
        $em->persist($reportSubmission);
        $em->flush();

        return $reportSubmission;
    }

    public function generateAndPersistSubmittedReportSubmission(EntityManager $em, DateTime $submitDate)
    {
        $rs = $this->generateAndPersistReportSubmission($em);
        $report = $rs->getReport()
            ->setSubmitDate($submitDate)
            ->setSubmitted(true);
        $rs->setCreatedOn($submitDate);

        $em->persist($rs);
        $em->persist($report);
        $em->flush();

        return $rs;
    }
}

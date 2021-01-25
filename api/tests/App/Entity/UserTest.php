<?php

namespace Tests\App\Entity;

use App\Entity\User as Entity;
use App\TestHelpers\ReportSubmissionHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\App\Entity\Abstracts\EntityTester;

/**
 * User Entity test
 */
class UserTest extends KernelTestCase
{

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetSetTeams()
    {
        $teams = new ArrayCollection(['foo']);

        $this->entity->setTeams($teams);

        $this->assertEquals($teams, $this->entity->getTeams());
    }

    public function testAddRemoveTeams()
    {
        $teams = new ArrayCollection(['foo']);

        $this->entity->setTeams($teams);

        $this->assertEquals($teams, $this->entity->getTeams());
    }

    /** @test */
    public function getNumberOfSubmittedReports()
    {
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $submissionHelper = new ReportSubmissionHelper();
        $submittedSubmissions = [];

        foreach (range(1, 2) as $index) {
            $submittedSubmissions[] = $submissionHelper->generateAndPersistSubmittedReportSubmission(
                $em,
                new DateTime()
            );
        }

        // Submit an extra report for first user
        $submissionHelper->submitAndPersistAdditionalSubmissions(
            $em,
            $submittedSubmissions[0]
        );

        // Create a report submission but dont submit it
        $notSubmittedSubmission = $submissionHelper->generateAndPersistReportSubmission($em);

        self::assertEquals(
            2,
            $submittedSubmissions[0]->getReport()->getClient()->getUsers()[0]->getNumberOfSubmittedReports()
        );

        self::assertEquals(
            1,
            $submittedSubmissions[1]->getReport()->getClient()->getUsers()[0]->getNumberOfSubmittedReports()
        );

        self::assertEquals(
            0,
            $notSubmittedSubmission->getReport()->getClient()->getUsers()[0]->getNumberOfSubmittedReports()
        );
    }
}

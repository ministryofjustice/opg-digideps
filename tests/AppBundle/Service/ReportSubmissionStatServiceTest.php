<?php

namespace AppBundle\Service;

use Mockery\Adapter\Phpunit\MockeryTestCase;

class ReportSubmissionStatServiceTest extends MockeryTestCase
{
    /**
     * @var ReportSubmissionStatService
     */
    protected $sut;

    /**
     * Set up the mockservies
     */
    public function setUp()
    {
        $this->sut = new ReportSubmissionStatService();
    }


    public function testgenerateReportSubmissionsCsvLines()
    {
        $rows = $this->sut->generateReportSubmissionsCsvLines([
                [
                    'id' => 24,
                    'report' => [
                        'client' => [
                            'firstname' => null,
                            'lastname' => null,
                            'caseNumber' => null,
                            'courtDate' => null,
                        ],
                        'startDate' => null,
                        'type' => 102,
                        'id' => 88,
                        'dueDate' => \DateTime::createFromFormat('d/m/Y', '2/5/2018'),
                        'submitDate' => \DateTime::createFromFormat('d/m/Y', '28/4/2018'),
                    ],
                    'createdBy' => [
                        'deputyNo' => null,
                        'email' => null,
                        'firstname' => null,
                        'lastname' => null,
                        'registrationDate' => null,
                        'lastLoggedIn' => null,
                        'id' => 1,
                    ],
                ],
                [
                    'id' => 25,
                    'ndr' => [
                        'client' => [
                            'firstname' => null,
                            'lastname' => null,
                            'caseNumber' => null,
                            'courtDate' => null,
                        ],
                        'startDate' => null,
                        'type' => 'ndr',
                        'id' => 92,
                        'dueDate' => \DateTime::createFromFormat('d/m/Y', '2/5/2018'),
                        'submitDate' => \DateTime::createFromFormat('d/m/Y', '28/4/2018'),
                    ],
                    'createdBy' => [
                        'deputyNo' => null,
                        'email' => null,
                        'firstname' => null,
                        'lastname' => null,
                        'registrationDate' => null,
                        'lastLoggedIn' => null,
                        'id' => 2,
                    ],
                    'client' => []
                ]
            ]
        );

        $this->assertEquals('id', $rows[0][0]);
        $this->assertEquals(24, $rows[1][0]);
        $this->assertEquals(25, $rows[2][0]);

        $this->assertEquals('report_due_date', $rows[0][7]);
        $this->assertEquals('02/05/2018', $rows[1][7]);

        $this->assertEquals('report_date_submitted', $rows[0][8]);
        $this->assertEquals('28/04/2018', $rows[1][8]);
    }

}

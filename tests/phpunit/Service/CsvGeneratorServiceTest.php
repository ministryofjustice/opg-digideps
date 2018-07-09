<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Expense;
use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use Common\Form\Elements\Validators\Money;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use MockeryStub as m;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class CsvGeneratorServiceTest extends MockeryTestCase
{
    /**
     * @var CsvGeneratorService
     */
    protected $sut;

    private $mockTranslator;
    private $mockLogger;
    private $mockReport;

    /**
     * Set up the mockservies
     */
    public function setUp()
    {
        $this->mockTranslator = m::mock(Translator::class);
        $this->mockLogger = m::mock(Logger::class);
        $this->mockLogger->shouldReceive('info')->with(m::type('String'));

        $this->mockTranslator->shouldReceive('trans')->with(m::type('string'), [], 'report-money-transaction')
            ->andReturn('SomeCategory');
        $this->sut = new CsvGeneratorService($this->mockTranslator, $this->mockLogger);
    }

    public function testGenerateTransactionsCsvNoTransactions()
    {
        $this->mockReport = $this->generateMockReport(99, 0, 0, 0, 0);

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertContains('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
    }

    public function testGenerateSubmissionsCsvNoSubmissions()
    {
        $csvString = $this->sut->generateReportSubmissionsCsv([]);
        $this->assertContains(
            'id,report_type,deputy_no,email,name,lastname,registration_date,report_due_date,report_date_submitted,last_logged_in,client_name,client_lastname,client_casenumber,client_court_order_date,total_reports,active_reports',
            $csvString
        );
    }

    public function testGenerateTransactionsCsvWtihTransactions()
    {
        $this->mockReport = $this->generateMockReport(
            99,
            20, // gifts
            20, // expenses
            50, // money out
            10 // money in
        );

        $csvString = $this->sut->generateTransactionsCsv($this->mockReport);
        $this->assertContains('Type,Category,Amount,"Bank name","Account details",Description', $csvString);
        $this->assertEquals(20, preg_match_all('/Gift/', $csvString));
        $this->assertEquals(20, preg_match_all('/Expense/', $csvString));
        $this->assertEquals(50, preg_match_all('/Money out/', $csvString));
        $this->assertEquals(10, preg_match_all('/Money in/', $csvString));

        $this->assertEquals(35, preg_match_all('/Custom bank name/', $csvString));
        $this->assertEquals(35, preg_match_all('/\(\*\*\*\* 1234\) 12\-34\-56\)/', $csvString));
    }

    public function testGenerateSubmissionsCsvWithSubmissions()
    {
        $csvString = $this->sut->generateReportSubmissionsCsv(
            [
                $this->generateMockReportSubmission(24, 88),
                $this->generateMockReportSubmission(25, 92)
            ]
        );
        $this->assertContains(
            '24,102,12345678,email+33@unittest.com,Firstname33,Lastname33,05/04/2005,05/02/2018,28/04/2018,03/02/2018,Firstname32,Lastname32,32323232,08/11/2011,64,1',
            $csvString
        );
        $this->assertContains(
            '25,102,12345678,email+33@unittest.com,Firstname33,Lastname33,05/04/2005,05/02/2018,28/04/2018,03/02/2018,Firstname32,Lastname32,32323232,08/11/2011,64,1',
            $csvString
        );
    }


    /**
     * Generates a mock Report with id and associated transactions
     *
     * @param $reportId
     * @param $numGifts
     * @param $numExpenses
     * @param $numMoneyOut
     * @param $numMoneyIn
     */
    private function generateMockReport($reportId, $numGifts = 0, $numExpenses = 0, $numMoneyOut = 0, $numMoneyIn = 0)
    {
        $mockReport = m::mock(ReportInterface::class);

        $mockReport->shouldReceive('getId')->andReturn($reportId);
        $mockReport->shouldReceive('getGifts')->andReturn(
            $this->generateMockTransactions(Gift::class, $numGifts)
        );
        $mockReport->shouldReceive('getExpenses')->andReturn(
            $this->generateMockTransactions(Expense::class, $numExpenses)
        );
        $mockReport->shouldReceive('getMoneyTransactionsOut')->andReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyOut)
        );
        $mockReport->shouldReceive('getMoneyTransactionsIn')->andReturn(
            $this->generateMockTransactions(MoneyTransaction::class, $numMoneyIn)
        );
        $mockReport->shouldReceive('getType')->andReturn(102);

        $mockReport->shouldReceive('getClient')->andReturn(
            $this->generateMockClient(32)
        );

        $mockReport->shouldReceive('getSubmitDate')->andReturn(new \DateTime('4/28/2018'));
        $mockReport->shouldReceive('getDueDate')->andReturn(new \DateTime('2/5/2018'));

        return $mockReport;
    }

    /**
     * Generates a mock Report with id and associated transactions
     *
     * @param $reportId
     * @param $numGifts
     * @param $numExpenses
     * @param $numMoneyOut
     * @param $numMoneyIn
     */
    private function generateMockNdr($ndrId)
    {
        $this->mockReport = m::mock(ReportInterface::class);

        $this->mockReport->shouldReceive('getId')->andReturn($ndrId);
        $this->mockReport->shouldReceive('getType')->andReturn('ndr');

    }

    /**
     * Generates a mock Report with id and associated transactions
     *
     * @param $reportId
     * @param $numGifts
     * @param $numExpenses
     * @param $numMoneyOut
     * @param $numMoneyIn
     */
    private function generateMockReportSubmission($reportSubmissionId, $reportId)
    {
        $mockReportSubmission = m::mock(ReportSubmission::class);

        $mockReportSubmission->shouldReceive('getId')->andReturn($reportSubmissionId);
        $mockReportSubmission->shouldReceive('getReport')->andReturn(
            $this->generateMockReport($reportId,0,0,0,0)
        );

        $mockReportSubmission->shouldReceive('getNdr')->andReturn(
            $this->generateMockNdr($reportId    )
        );

        $mockReportSubmission->shouldReceive('getCreatedBy')->andReturn(
            $this->generateMockUser(33)
        );

        return $mockReportSubmission;
    }


    /**
     * Generates $qty of Mock entited of class $class
     *
     * @param $class
     * @param $qty
     * @return array
     */
    private function generateMockTransactions($class, $qty)
    {
        $mocks = [];
        for ($i=0; $i < $qty; $i++) {
            array_push($mocks, $this->generateMockTransactionEntity($class, $i));
        }
        return $mocks;
    }

    /**
     * Generates instance of mock Entity $class. Counter used to differentiate properties only.
     *
     * @param $class
     * @param $counter
     * @return mixed
     */
    private function generateMockTransactionEntity($class, $counter)
    {
        $mock = new $class();
        $mock->setAmount(10.00);
        //$mock->setIsJointAccount(true);
        switch ($class) {
            case Gift::class:
                $mock->setExplanation('explanation for gift ' . $counter);
                break;
            case Expense::class:
                $mock->setExplanation('explanation for expense ' . $counter);
                break;
            case MoneyTransaction::class:
                // Assign Category based on counter
                $mock->setCategory(MoneyTransaction::$categories[min($counter, count(MoneyTransaction::$categories)-1)][0]);
                $mock->setDescription('description for transaction ' . $counter);

                break;
        }


        // set all even numbers to have a bank account
        $bankAccount = ($counter%3 == 0) ? $this->generateBankAccount($counter) : null;
        $mock->setBankAccount($bankAccount);

        return $mock;
    }

    /**
     * Generates instance of mock bank account. $counter used to differentiate.
     *
     * @param $counter
     * @return \Mockery\Mock
     */
    private function generateBankAccount($counter)
    {
        $mockBankAccount = m::mock(BankAccount::class)->makePartial();
        $mockBankAccount->shouldReceive('getDisplayName')->andReturn('(**** 1234) 12-34-56)');
        $mockBankAccount->shouldReceive('getBank')->andReturn('Custom bank name ' . $counter);

        return $mockBankAccount;
    }

    /**
     * Generates instance of mock user
     *
     * @param $counter
     * @return \Mockery\Mock
     */
    private function generateMockUser($counter)
    {
        $mock = m::mock(User::class)->makePartial();
        $mock->shouldReceive('getFirstname')->andReturn('Firstname' . $counter);
        $mock->shouldReceive('getLastname')->andReturn('Lastname' . $counter);
        $mock->shouldReceive('getEmail')->andReturn('email+' . $counter . '@unittest.com');
        $mock->shouldReceive('getRegistrationDate')->andReturn(new \DateTime('4/5/2005'));
        $mock->shouldReceive('getLastLoggedIn')->andReturn(new \DateTime('2/3/2018'));
        $mock->shouldReceive('getDeputyNo')->andReturn(12345678);

        return $mock;
    }

    /**
     * Generates instance of mock user
     *
     * @param $counter
     * @return \Mockery\Mock
     */
    private function generateMockClient($counter)
    {
        $mock = m::mock(Client::class)->makePartial();
        $mock->shouldReceive('getFirstname')->andReturn('Firstname' . $counter);
        $mock->shouldReceive('getLastname')->andReturn('Lastname' . $counter);
        $mock->shouldReceive('getCaseNumber')->andReturn($counter . $counter . $counter . $counter);
        $mock->shouldReceive('getTotalReportCount')->andReturn($counter * 2);
        $mock->shouldReceive('getActiveReportCount')->andReturn(1);
        $mock->shouldReceive('getCourtDate')->andReturn(new \DateTime('11/8/2011'));

        return $mock;
    }

}

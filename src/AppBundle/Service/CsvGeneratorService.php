<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CsvGeneratorService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CSV file pointer
     */
    private $fd;

    /**
     * CsvGeneratorService constructor.
     * @param TranslatorInterface $translator
     * @param LoggerInterface     $logger
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->logger =$logger;
    }

    /**
     * Generate the report transactions as csv
     *
     * @param ReportInterface $report
     *
     * @return string Csv content
     */
    public function generateTransactionsCsv(ReportInterface $report)
    {
        $this->logger->info('Generating Transaction CSV for Report: ' . $report->getId());

        $this->initialiseFilePointer();

        $this->generateTransactionsCsvLines($report);

        rewind($this->fd);
        $csvContent = stream_get_contents($this->fd);
        fclose($this->fd);

        return $csvContent;
    }

    /**
     * Generate all the report submissions as csv
     *
     * @param array $records
     *
     * @return string Csv content
     */
    public function generateReportSubmissionsCsv($records)
    {
        $this->logger->info('Generating Report submissions CSV : ');

        $this->initialiseFilePointer();

        $this->generateReportSubmissionsCsvLines($records);

        rewind($this->fd);
        $csvContent = stream_get_contents($this->fd);
        fclose($this->fd);

        return $csvContent;
    }

    /**
     * Initialise file pointer to php memory for CSV creation
     *
     * @throws \Exception
     */
    private function initialiseFilePointer()
    {
        // initialize temporary fp
        $this->fd = fopen('php://temp/maxmemory:1048576', 'w');
        if ($this->fd === false) {
            $this->logger->error('Failed to open Temporary file');
            throw new \Exception('Failed to open temporary file');
        }
    }

    /**
     * Generates the lines of the CSV
     *
     * @param ReportInterface $report
     */
    private function generateTransactionsCsvLines(ReportInterface $report)
    {
        //foreach($report->getBankAccounts() as $bankAccount) {
        //$this->generateBankAccountSummary($bankAccount);
        $headers = ['Type', 'Category','Amount', 'Bank name', 'Account details', 'Description'];
        $this->generateCsvHeaders($headers);
        $this->generateTransactionRows($report->getGifts(), 'gift');
        $this->generateTransactionRows($report->getExpenses(), 'expense');
        $this->generateTransactionRows($report->getMoneyTransactionsOut(), 'money out');
        $this->generateTransactionRows($report->getMoneyTransactionsIn(), 'money in');

        //}
    }

    /**
     * Generates the lines of the CSV
     *
     * @param ReportInterface $report
     */
    private function generateReportSubmissionsCsvLines($records)
    {
        $headers = [
            'id', 'report_type', 'deputy_no', 'email','name', 'lastname', 'registration_date', 'report_due_date', 'report_date_submitted',
            'last_logged_in', 'client_name', 'client_lastname', 'client_casenumber', 'client_court_order_date',
            'total_reports', 'active_reports'
        ];
        $this->generateCsvHeaders($headers);
        $this->generateReportSubmissionRows($records);
    }

    /**
     * Generates a Bank Account Summary to the CSV file pointer
     *
     * @param BankAccount $bankAccount
     */
    private function generateBankAccountSummary(BankAccount $bankAccount)
    {
        // @todo remove following feedback on whether an account summary is required
        $summaryFields = [
            [' '],
            [' '],
            ['ACCOUNT SUMMARY'],
            [$bankAccount->getBank() . '   -   ' . ucfirst($bankAccount->getAccountType()) . ' account' ],
            ['**** ' . $bankAccount->getAccountNumber() . '      (' . $bankAccount->getSortCode() . ')'],
            [$bankAccount->getIsJointAccount() ? 'JOINT ACCOUNT' : ''],
            [' '],
            [' '],


        ];

        foreach ($summaryFields as $line) {
            fputcsv($this->fd, $line);
        }
    }

    /**
     * Generate CSV Headers
     */
    private function generateCsvHeaders($headers)
    {
        if (!empty($headers)) {
            fputcsv($this->fd, $headers);
        }
    }

    /**
     * Generates Transaction row
     *
     * @param $transactions
     * @param $type
     */
    private function generateTransactionRows($transactions, $type)
    {
        foreach ($transactions as $t) {
            /** @var $t \AppBundle\Entity\Report\MoneyTransaction */
            fputcsv(
                $this->fd, [
                    ucfirst($type),
                    (property_exists($t, 'category') ?
                        $this->translator->trans(
                            'form.category.entries.' . $t->getCategory() . '.label', [], 'report-money-transaction') : ''),
                    $t->getAmount(),
                    (!empty($t->getBankAccount()) ? $t->getBankAccount()->getBank() : ''),
                    (!empty($t->getBankAccount()) ? $t->getBankAccount()->getDisplayName() : ''),
                    $this->generateDescription($t)
                ]
            );
        }
    }

    /**
     * Generates Report submission row
     *
     * @param $records
     * @param $type
     */
    private function generateReportSubmissionRows($records)
    {
        foreach ($records as $rs) {
            /** @var $rs \AppBundle\Entity\Report\ReportSubmission */
            $report = $rs->getReport();
            if (empty($report)) {
                $report = $rs->getNdr();
            }

            if (!empty($report)) {
                fputcsv($this->fd, $this->generateDataRow($rs, $report));
            }
        }
    }

    /**
     * Takes an instance of ReportSubmission and ReportInterface and returns a summary of data relating to the
     * report submission. Returned to generate and sanitize the data to ensure CSV generation does not fail.
     *
     * @param ReportSubmission $rs
     * @param ReportInterface $reportOrNdr
     * @return array
     */
    private function generateDataRow(ReportSubmission $rs, ReportInterface $reportOrNdr)
    {
        $data = [];
        array_push($data, $rs->getId());
        array_push($data, $reportOrNdr->getType());
        $createdBy = $rs->getCreatedBy();

        if ($createdBy instanceof User) {
            array_push($data, $createdBy->getDeputyNo());
            array_push($data, $createdBy->getEmail());
            array_push($data, $createdBy->getFirstname());
            array_push($data, $createdBy->getLastname());
            array_push($data, $this->outputDate($rs->getCreatedBy()->getRegistrationDate()));
        } else {
            array_push($data, null);
            array_push($data, null);
            array_push($data, null);
            array_push($data, null);
            array_push($data, null);
        }

        array_push($data, $this->outputDate($reportOrNdr->getDueDate()));
        array_push($data, $this->outputDate($reportOrNdr->getSubmitDate()));

        if ($createdBy instanceof User) {
            array_push($data, $this->outputDate($rs->getCreatedBy()->getLastLoggedIn()));
        } else {
            array_push($data, null);
        }
        array_push($data, $reportOrNdr->getClient()->getFirstname());
        array_push($data, $reportOrNdr->getClient()->getLastname());
        array_push($data, $reportOrNdr->getClient()->getCaseNumber());
        array_push($data, $this->outputDate($reportOrNdr->getClient()->getCourtDate()));
        array_push($data, $reportOrNdr->getClient()->getTotalReportCount());
        array_push($data, $reportOrNdr->getClient()->getActiveReportCount());

        return $data;
    }
    
    /**
     * Output a formatted string from a given \DateTime object if set.
     *
     * @param null|\DateTime $date
     * @return null|string
     */
    private function outputDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date->format('d/m/Y');
        }
        return null;
    }

    /**
     * Generates a description. Expenses and Gifts have an 'explanation' property,
     * Money transactions have a description property.
     *
     * @param $transaction
     * @return string
     */
    private function generateDescription($transaction)
    {
        if (method_exists($transaction, 'getDescription')) {
            return $transaction->getDescription();
        }

        if (method_exists($transaction, 'getExplanation')) {
            return $transaction->getExplanation();
        }
        return '';
    }
}

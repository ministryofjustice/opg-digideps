<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\File\Storage\S3Storage;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
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
     * CsvGenerator constructor.
     *
     * @param Container $container
     * @throws \Exception
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->logger =$logger;
    }

    /**
     * Generate the report transactions as csv
     *
     * @param Report $report
     *
     * @return string Csv content
     */
    public function generateTransactionsCsv(ReportInterface $report)
    {
        $this->logger->info('Generating Transaction CSV for Report: ' . $report->getId());

        // initialize temporary fp
        $this->fd = fopen('php://temp/maxmemory:1048576', 'w');
        if($this->fd === FALSE) {
            $this->logger->error('Failed to open Temporary file');
            die('Failed to open temporary file');
        }

        $this->generateTransactionsCsvLines($report);

        rewind($this->fd);
        $csvContent = stream_get_contents($this->fd);
        fclose($this->fd);

        return $csvContent;
    }

    /**
     * Generates the lines of the CSV
     *
     * @param Report $report
     */
    private function generateTransactionsCsvLines(ReportInterface $report)
    {
        //foreach($report->getBankAccounts() as $bankAccount) {
        //$this->generateBankAccountSummary($bankAccount);
        $this->generateCsvHeaders();
        $this->generateTransactionRows($report->getGifts(), 'gift');
        $this->generateTransactionRows($report->getExpenses(), 'expenses');
        $this->generateTransactionRows($report->getMoneyTransactionsOut(), 'money out');
        $this->generateTransactionRows($report->getMoneyTransactionsIn(), 'money in');

        //}
    }

    /**
     * Generates a Bank Account Summary
     *
     * @param BankAccount $bankAccount
     */
    private function generateBankAccountSummary(BankAccount $bankAccount)
    {
        $summaryFields = [
            [" "],
            [" "],
            ["ACCOUNT SUMMARY"],
            [$bankAccount->getBank() . "   -   " . ucfirst($bankAccount->getAccountType()) ." account" ],
            ["**** " . $bankAccount->getAccountNumber() . "      (" . $bankAccount->getSortCode(). ")"],
            [$bankAccount->getIsJointAccount() ? "JOINT ACCOUNT" : ""],
            [" "],
            [" "],


        ];

        foreach($summaryFields as $line)
        {
            fputcsv($this->fd, $line);
        }
    }

    /**
     * Generate CSV Headers
     */
    private function generateCsvHeaders()
    {
        $headers = ['Type', 'Category' ,'Amount', 'Account', 'Description'];
        fputcsv($this->fd, $headers);
    }

    /**
     * Generates Transaction row
     *
     * @param $transactions
     * @param $type
     */
    private function generateTransactionRows($transactions, $type)
    {
        foreach($transactions as $t) {
            /** @var $t \AppBundle\Entity\Report\MoneyTransaction */
            fputcsv(
                $this->fd, [
                    ucFirst($type),
                    (property_exists($t, 'category') ?
                        $this->translator->trans(
                            'form.category.entries.' . $t->getCategory().'.label', [], 'report-money-transaction') : ''),
                    $t->getAmount(),
                    (!empty($t->getBankAccount()) ? $t->getBankAccount()->getDisplayName() : "UNASSIGNED"),
                    (property_exists($t, 'description') ? $t->getDescription() : '')
                ]
            );
        }
    }
}

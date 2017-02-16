<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;

class PaService
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(EntityDir\User::class);
        $this->reportRepository = $em->getRepository(EntityDir\Report\Report::class);
        $this->clientRepository = $em->getRepository(EntityDir\Client::class);
        $this->log = [];
    }

    /**
     *
     * Example of a single row :[
     *     'Email'        => 'dep2@provider.com',
     *     'Deputy No'    => '00000001',
     *     'Dep Postcode' => 'N1 ABC',
     *     'Dep Forename' => 'Dep1',
     *     'Dep Surname'  => 'Uty2',
     *     'Dep Type'     => 23,
     *     'Dep Adrs1'    => 'ADD1',
     *     'Dep Adrs2'    => 'ADD2',
     *     'Dep Adrs3'    => 'ADD3',
     *     'Dep Adrs4'    => 'ADD4',
     *     'Dep Adrs5'    => 'ADD5',
     *
     *     'Case'       => '10000003',
     *     'Forename'   => 'Cly3',
     *     'Surname'    => 'Hent3',
     *     'Corref'     => 'A3',
     *     'Report Due' => '05-Feb-15',
     * ]
     *
     * @param array $rows
     *
     * @return array
     */
    public function addFromCasrecRows(array $rows)
    {
        $added = ['users' => [], 'clients' => [], 'reports' => []];
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($row['Dep Type'] != 23) {
                continue;
            }

            // find or create deputy
            $email = $row['Email'];
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if (!$user) {
                $user = new EntityDir\User();
                $user->setDeputyNo($row['Deputy No'])
                    ->setEmail($email)
                    ->setFirstname($row['Dep Forename'])
                    ->setLastname($row['Dep Surname'])
                    ->setRoleName(EntityDir\User::ROLE_PA)
                    ->setAddress1($row['Dep Adrs1'])
                    ->setAddress2($row['Dep Adrs2'])
                    ->setAddress3($row['Dep Adrs3'] . ' ' . $row['Dep Adrs4'] . ' ' . $row['Dep Adrs5'])
                    ->setAddressPostcode($row['Dep Postcode'])//->setAddressCountry('GB')
                ;
                $added['users'][] = $email;
                $this->em->persist($user);
                $this->em->flush($user);
            }

            // find or create client
            $caseNumber = $row['Case'];
            $client = $user->getClientByCaseNumber($caseNumber);
            if (!$client) {
                $client = new EntityDir\Client();
                $client
                    ->setCaseNumber($caseNumber)
                    ->setFirstname($row['Forename'])
                    ->setLastname($row['Surname'])//->setCourtDate($row['Dship Create'])
                ;
                $added['clients'][] = $caseNumber;
                $this->em->persist($client);
                $user->addClient($client);
                $this->em->persist($user);
                $this->em->flush($user);
                $this->em->flush($client);
            }

            // find or create reports
            $reportDueDate = self::parseDate($row['Report Due']);
            if (!$reportDueDate) {
                $errors []= "Cannot parse date {$row['Report Due']} in line $index";
            } else {
                $report = $client->getReportByDueDate($reportDueDate);
                if (!$report) {
                    $report = new EntityDir\Report\Report();
                    $client->addReport($report);
                    $report
                        ->setType(EntityDir\Report\Report::TYPE_102)
                        ->setEndDate($reportDueDate);
                    $added['reports'][] = $client->getCaseNumber() . '-' . $reportDueDate->format('Y-m-d');
                    $this->em->persist($report);
                    $this->em->flush();
                }
            }

            // next row
            $this->em->clear();
        }

        return ['added' => $added, 'errors'=>$errors];
    }

    /**
     * create DateTime object based on '16-Dec-2014' formatted dates
     *
     * @param string $dateString e.g. 16-Dec-2014
     *
     * @return \DateTime
     */
    private static function parseDate($dateString)
    {
        return \DateTime::createFromFormat('d-M-Y', $dateString);
    }
}

<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;

class CasrecService
{
    const STATS_NOT_OLDER_THAN = '-60 minutes';

    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function updateOne(CasRec $casrec)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['deputyNo' => $casrec->getDeputyNo()]);
        if ($user instanceof User) {
            $casrec->setLastLoggedIn($user->getLastLoggedIn())->setRegistrationDate($user->getRegistrationDate());
        }

        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $casrec->getCaseNumber()]);
        if ($client instanceof Client) {
            $casrec
                ->setNOfReportsSubmitted(count($client->getSubmittedReports()))
                ->setNOfReportsActive(count($client->getUnsubmittedReports()));
        }

        $casrec->setUpdatedAt(new \DateTime());
    }

    /**
     * Launhed
     * @return int number of changed records
     */
    public function updateAll()
    {
        $chunkSize = 50;
        $nOfRecordsUpdated = 0;

        while ($records = $this->em
            ->createQuery('SELECT c from ' . CasRec::class . ' c WHERE  (c.updatedAt < :d OR c.updatedAt IS NULL) ORDER BY c.updatedAt ASC')
            ->setParameter('d', new \DateTime(self::STATS_NOT_OLDER_THAN))
            ->setMaxResults($chunkSize)->getResult()) {

            foreach ($records as $record) {
                /* @var $nextRecordToUpdate CasRec */
                $this->updateOne($record);
                $nOfRecordsUpdated++;
            }
            $this->em->flush();
            $this->em->clear();
        }

        return $nOfRecordsUpdated;
    }

    /**
     * @param string $filePath
     * @param int $maxResults
     *
     * @return string
     */
    public function saveCsv($filePath)
    {
        $linesWritten = 0;

        /* @var $it IterableResult */
        // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html
        $it = $this->em->createQuery('SELECT c FROM '.CasRec::class.' c')->iterate();

        $f = fopen($filePath, 'w');
        foreach ($it as $itRow) {
            $row = $itRow[0]->toArray();
            if ($it->key() === 0) { // write header (only for first row)
                fputcsv($f, array_keys($row));
            }
            fputcsv($f, $row);
            $linesWritten++;
        }
        fclose($f);

        return $linesWritten;
    }
}

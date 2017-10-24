<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use Doctrine\ORM\EntityManager;
use \Doctrine\Common\Util\Debug as doctrineDebug;

class CasrecVerificationService
{
    /** @var EntityManager */
    private $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $casRecRepo;

    private $lastMatchedCasrecUsers;

    public function __construct($em)
    {
        $this->em = $em;
        $this->casRecRepo = $this->em->getRepository('AppBundle\Entity\CasRec');
        $this->lastMatchedCasrecUsers = [];
    }

    /**
     * CASREC checks
     * Throw error 400 if casrec has no record matching case number,
     * client surname, deputy surname, and postcode (if set)
     *
     * @param string $caseNumber
     * @param string $clientSurname
     * @param string $deputySurname
     * @param string $deputyPostcode
     * @return bool
     */
    public function validate($caseNumber, $clientSurname, $deputySurname, $deputyPostcode)
    {
        $crMatches = $this->casRecRepo->findBy( [ 'caseNumber'     => CasRec::normaliseCaseNumber($caseNumber)
                                                , 'clientLastname' => CasRec::normaliseSurname($clientSurname)
                                                , 'deputySurname'  => CasRec::normaliseSurname($deputySurname)
                                                ]);

        $this->lastMatchedCasrecUsers = $this->applyPostcodeFilter($crMatches, $deputyPostcode);

        if (count($this->lastMatchedCasrecUsers) == 0) {
            throw new \RuntimeException('User registration: no matching record in casrec', 400);
        }

        return true;
    }

    /**
     * Since co-deputies, multiple deputies may be matched (eg siblings at same postcode)
     *
     * @return array
     */
    public function getLastMatchedDeputyNumbers()
    {
        $deputyNumbers = [];
        foreach ($this->lastMatchedCasrecUsers as $casRecMatch) {
            $deputyNumbers[] = $casRecMatch->getDeputyNo();
        }
        return $deputyNumbers;
    }

    /**
     * @param string $caseNumber
     * @return bool
     */
    public function isMultiDeputyCase($caseNumber)
    {
        $crMatches = $this->casRecRepo->findByCaseNumber(CasRec::normaliseCaseNumber($caseNumber));
        return count($crMatches) > 1;
    }

    /**
     * @param CasRec[] $crMatches
     * @param $deputyPostcode
     * @return CasRec[]
     */
    private function applyPostcodeFilter($crMatches, $deputyPostcode)
    {
        $deputyPostcode = CasRec::normalisePostCode($deputyPostcode);
        $crByPostcode = [];
        $crWithPostcodeCount = 0;
        foreach ($crMatches as $crMatch) {
            $crMatchPC = CasRec::normalisePostCode($crMatch->getDeputyPostCode());
            if (!empty($crMatchPC)) {
                $crByPostcode[$crMatchPC][] = $crMatch;
                $crWithPostcodeCount++;
            }
        }

        $filteredResults  = ($crWithPostcodeCount < count($crMatches))
            ? $crMatches
            : $crByPostcode[$deputyPostcode];

        return $filteredResults;
    }
}
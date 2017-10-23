<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use Doctrine\ORM\EntityManager;

class CasrecVerificationService
{
    /** @var EntityManager */
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * CASREC checks
     * Throw error 421 if casrec has no record matching case number,
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
        $criteria = [ 'caseNumber'     => CasRec::normaliseCaseNumber($caseNumber)
                    , 'clientLastname' => CasRec::normaliseSurname($clientSurname)
                    , 'deputySurname'  => CasRec::normaliseSurname($deputySurname)
                    ];

        try {
            $casRecUserMatches = $this->getCasRecMatchesOrThrowError($criteria);
            $this->checkPostcodeExistsInCasRec($casRecUserMatches, $deputyPostcode);
        } catch (\Exception $e) {
            throw new \RuntimeException('User registration: no matching record in casrec', 400);
        }

        return true;
    }

    /**
     * @param array $criteria
     * @return CasRec[]
     */
    private function getCasRecMatchesOrThrowError($criteria)
    {
        $casRecMatches = $this->em->getRepository('AppBundle\Entity\CasRec')->findBy($criteria);
        if (count($casRecMatches) == 0) {
            throw new \RuntimeException();
        }
        return $casRecMatches;
    }

    /**
     * @param array $casRecUsers
     * @param string $postcode
     */
    private function checkPostcodeExistsInCasRec($casRecUsers, $postcode)
    {
        // Now that multi deputies are a thing, best we can do is ensure that the given postcode matches ONE of the postcodes
        // (Or skip this check completely it if one of the postcodes isn't set)
        $casRecPostcodes = [];
        foreach ($casRecUsers as $casRecMatch) {
            if (!empty($casRecMatch->getDeputyPostCode())) {
                $casRecPostcodes[] = $casRecMatch->getDeputyPostCode();
            }
        }
        if (count($casRecPostcodes) == count($casRecUsers)) {
            if (!in_array(CasRec::normalisePostCode($postcode), $casRecPostcodes)){
                throw new \RuntimeException();
            }
        }
    }
}
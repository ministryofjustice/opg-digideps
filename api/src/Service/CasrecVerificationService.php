<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class CasrecVerificationService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var ObjectRepository */
    private $casRecRepo;

    /**
     * @var CasRec[]
     */
    private $lastMatchedCasrecUsers;

    public function __construct(EntityManagerInterface $em)
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
     *
     * @return bool
     */
    public function validate($caseNumber, $clientSurname, $deputySurname, $deputyPostcode)
    {
        /** @var CasRec[] $crMatches */
        $crMatches = $this->casRecRepo->findBy([
            'caseNumber'     => $this->normaliseCaseNumber($caseNumber),
            'clientLastname' => $this->normaliseSurname($clientSurname),
            'deputySurname'  => $this->normaliseSurname($deputySurname)
        ]);

        $this->lastMatchedCasrecUsers = $this->applyPostcodeFilter($crMatches, $deputyPostcode);

        if (count($this->lastMatchedCasrecUsers) == 0) {
            throw new \RuntimeException('User registration: no matching record in casrec. Matched: ' . count($crMatches) . ' Looking up:' .
            ' Case Number: ' . $this->normaliseCaseNumber($caseNumber) .
            ' Client Last name: ' . $this->normaliseSurname($clientSurname) .
            ' Deputy surname:' . $this->normaliseSurname($deputySurname) .
            ' Filtered by deputy postcode: ' . $deputyPostcode, 400);
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
     * @return bool true if at least one matched CASREC contains NDR flag set to true
     */
    public function isLastMachedDeputyNdrEnabled()
    {
        foreach ($this->lastMatchedCasrecUsers as $casRecMatch) {
            if ($casRecMatch->getColumn('NDR')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $caseNumber
     *
     * @return bool
     */
    public function isMultiDeputyCase($caseNumber)
    {
        $crMatches = $this->casRecRepo->findByCaseNumber($this->normaliseCaseNumber($caseNumber));
        return count($crMatches) > 1;
    }

    /**
     * @param CasRec[] $crMatches
     * @param string $deputyPostcode
     *
     * @return CasRec[]
     */
    private function applyPostcodeFilter(array $crMatches, string $deputyPostcode)
    {
        $deputyPostcode = $this->normalisePostCode($deputyPostcode);
        $crByPostcode = [];
        $crWithPostcodeCount = 0;
        foreach ($crMatches as $crMatch) {
            $crMatchPC = $this->normalisePostCode($crMatch->getDeputyPostCode());
            if (!empty($crMatchPC)) {
                $crByPostcode[$crMatchPC][] = $crMatch;
                $crWithPostcodeCount++;
            }
        }

        if ($crWithPostcodeCount < count($crMatches)) {
            $filteredResults = $crMatches;
        } else {
            $filteredResults = array_key_exists($deputyPostcode, $crByPostcode) ? $crByPostcode[$deputyPostcode] : [];
        }

        return $filteredResults;
    }

    /**
     * @param string $caseNumber
     *
     * @return mixed|string
     */
    private function normaliseCaseNumber(string $caseNumber)
    {
        $caseNumber = trim($caseNumber);
        $caseNumber = strtolower($caseNumber);
        $caseNumber = preg_replace('#^([a-z0-9]+/)#i', '', $caseNumber);

        return $caseNumber;
    }

    /**
     * @param string $postcode
     *
     * @return string
     */
    private function normalisePostcode(string $postcode)
    {
        $postcode = trim($postcode);
        $postcode = strtolower($postcode);
        // remove MBE suffix
        /** @var string $postcode */
        $postcode = preg_replace('/ (mbe|m b e)$/i', '', $postcode);
        // remove characters that are not a-z or 0-9 or spaces
        /** @var string $postcode */
        $postcode = preg_replace('/([^a-z0-9])/i', '', $postcode);

        return $postcode;
    }

    /**
     * @param string $surname
     *
     * @return mixed|string
     */
    private function normaliseSurname(string $surname)
    {
        $surname = trim($surname);
        $surname = strtolower($surname);
        $normalizeChars = [
            'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A',
            'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I',
            'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a',
            'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'ü' => 'u', 'û' => 'u',
            'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f', 'ă' => 'a', 'ș' => 's', 'ț' => 't', 'Ă' => 'A', 'Ș' => 'S',
            'Ț' => 'T',
        ];
        $surname = strtr($surname, $normalizeChars);
        // remove MBE suffix
        /** @var string $surname */
        $surname = preg_replace('/ (mbe|m b e)$/i', '', $surname);
        // remove characters that are not a-z or 0-9 or spaces
        /** @var string $surname */
        $surname = preg_replace('/([^a-z0-9])/i', '', $surname);

        return $surname;
    }
}

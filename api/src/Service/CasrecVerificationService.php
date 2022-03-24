<?php

namespace App\Service;

use App\Entity\CasRec;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

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
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->casRecRepo = $this->em->getRepository('App\Entity\CasRec');
        $this->lastMatchedCasrecUsers = [];
        $this->serializer = $serializer;
    }

    /**
     * CASREC checks
     * Throw error 400 if casrec has no record matching case number,
     * client surname, deputy surname, and postcode (if set).
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
        $caseNumberMatches = $this->casRecRepo->findBy(['caseNumber' => $caseNumber]);

        $detailsToMatchOn = [
            'caseNumber' => $caseNumber,
            'clientLastname' => $clientSurname,
            'deputySurname' => $deputySurname,
        ];

        /** @var CasRec[] $crMatches */
        $allDetailsMatches = $this->casRecRepo->findBy($detailsToMatchOn);

        $this->lastMatchedCasrecUsers = $this->applyPostcodeFilter($allDetailsMatches, $deputyPostcode);

        if (0 == count($this->lastMatchedCasrecUsers)) {
            $detailsToMatchOn['deputyPostcode'] = $deputyPostcode;

            $caseNumberMatches = json_decode(
                $this->serializer->serialize($caseNumberMatches, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['otherColumns']]),
                true
            );

            $errorJson = json_encode([
                'search_terms' => $detailsToMatchOn,
                'case_number_matches' => $caseNumberMatches,
            ]);

            throw new RuntimeException($errorJson, 400);
        }

        return true;
    }

    /**
     * Since co-deputies, multiple deputies may be matched (eg siblings at same postcode).
     *
     * @return array
     */
    public function getLastMatchedDeputyNumbers()
    {
        $deputyNumbers = [];
        foreach ($this->lastMatchedCasrecUsers as $casRecMatch) {
            $deputyNumbers[] = $casRecMatch->getDeputyUid();
        }

        return $deputyNumbers;
    }

    /**
     * @return bool true if at least one matched CASREC contains NDR flag set to true
     */
    public function isLastMachedDeputyNdrEnabled()
    {
        foreach ($this->lastMatchedCasrecUsers as $casRecMatch) {
            if ($casRecMatch->getNdr()) {
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
        $crMatches = $this->casRecRepo->findByCaseNumber($caseNumber);

        return count($crMatches) > 1;
    }

    /**
     * @return CasRec[]
     */
    private function applyPostcodeFilter(mixed $casrecMatches, string $deputyPostcode)
    {
        $casrecByPostcode = [];
        $casrecWithPostcodeCount = 0;
        foreach ($casrecMatches as $casrecMatch) {
            $postcode = $casrecMatch->getDeputyPostCode();

            if (!empty($casrecMatch->getDeputyPostCode())) {
                $casrecByPostcode[$postcode][] = $casrecMatch;
                ++$casrecWithPostcodeCount;
            }
        }

        if ($casrecWithPostcodeCount < count($casrecMatches)) {
            $filteredResults = $casrecMatches;
        } else {
            $filteredResults = array_key_exists($deputyPostcode, $casrecByPostcode) ? $casrecByPostcode[$deputyPostcode] : [];
        }

        return $filteredResults;
    }
}

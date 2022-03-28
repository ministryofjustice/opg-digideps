<?php

namespace App\Service;

use App\Entity\PreRegistration;
use App\Repository\PreRegistrationRepository;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class PreRegistrationVerificationService
{
    /**
     * @var PreRegistration[]
     */
    private $lastMatchedPreRegistrationUsers;

    public function __construct(private SerializerInterface $serializer, private PreRegistrationRepository $preRegistrationRepository)
    {
        $this->lastMatchedPreRegistrationUsers = [];
    }

    /**
     * Throw error 400 if preregistration has no record matching case number,
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
        $caseNumberMatches = $this->preRegistrationRepository->findBy(['caseNumber' => $caseNumber]);

        $detailsToMatchOn = [
            'caseNumber' => $caseNumber,
            'clientLastname' => $clientSurname,
            'deputySurname' => $deputySurname,
        ];

        /** @var PreRegistration[] $crMatches */
        $allDetailsMatches = $this->preRegistrationRepository->findBy($detailsToMatchOn);

        $this->lastMatchedPreRegistrationUsers = $this->applyPostcodeFilter($allDetailsMatches, $deputyPostcode);

        if (0 == count($this->lastMatchedPreRegistrationUsers)) {
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
        foreach ($this->lastMatchedPreRegistrationUsers as $match) {
            $deputyNumbers[] = $match->getDeputyUid();
        }

        return $deputyNumbers;
    }

    /**
     * @return bool true if at least one matched PreRegistration contains NDR flag set to true
     */
    public function isLastMachedDeputyNdrEnabled()
    {
        foreach ($this->lastMatchedPreRegistrationUsers as $match) {
            if ($match->getNdr()) {
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
        $crMatches = $this->preRegistrationRepository->findByCaseNumber($caseNumber);

        return count($crMatches) > 1;
    }

    /**
     * @return PreRegistration[]
     */
    private function applyPostcodeFilter(mixed $preRegistrationMatches, string $deputyPostcode)
    {
        $preRegistrationByPostcode = [];
        $preREgistrationWithPostcodeCount = 0;
        foreach ($preRegistrationMatches as $match) {
            $postcode = $match->getDeputyPostCode();

            if (!empty($match->getDeputyPostCode())) {
                $preRegistrationByPostcode[$postcode][] = $match;
                ++$preREgistrationWithPostcodeCount;
            }
        }

        if ($preREgistrationWithPostcodeCount < count($preRegistrationMatches)) {
            $filteredResults = $preRegistrationMatches;
        } else {
            $filteredResults = array_key_exists($deputyPostcode, $preRegistrationByPostcode) ? $preRegistrationByPostcode[$deputyPostcode] : [];
        }

        return $filteredResults;
    }
}

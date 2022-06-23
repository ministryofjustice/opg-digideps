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
    private array $lastMatchedPreRegistrationUsers;

    public function __construct(private SerializerInterface $serializer, private PreRegistrationRepository $preRegistrationRepository)
    {
        $this->lastMatchedPreRegistrationUsers = [];
    }

    /**
     * Throw error 400 if preregistration has no record matching case number,
     * client surname, deputy surname, and postcode (if set).
     */
    public function validate(string $caseNumber, string $clientSurname, string $deputySurname, ?string $deputyPostcode): bool
    {
        $detailsToMatchOn = [
            'caseNumber' => $caseNumber,
            'clientLastname' => $clientSurname,
            'deputySurname' => $deputySurname,
        ];

        if ($deputyPostcode) {
            $detailsToMatchOn['deputyPostcode'] = $deputyPostcode;
        }

        $caseNumberMatches = $this->getCaseNumberMatches($detailsToMatchOn);

        $clientLastnameMatches = $this->filterByClientLastname($caseNumberMatches, $detailsToMatchOn);

        $deputyLastnameMatches = $this->filterByDeputyLastname($clientLastnameMatches, $detailsToMatchOn);

        if ($deputyPostcode) {
            $this->lastMatchedPreRegistrationUsers = $this->applyPostcodeFilter($deputyLastnameMatches, $deputyPostcode);

            if (0 == count($this->lastMatchedPreRegistrationUsers)) {
                $deputyLastnameMatches = $this->formatPreRegistraionMatchesForErrorOutput($deputyLastnameMatches);

                $errorJson = json_encode([
                    'search_terms' => $detailsToMatchOn,
                    'deputy_last_name_matches' => $deputyLastnameMatches,
                ]);

                throw new RuntimeException($errorJson, 400);
            }
        }

        return true;
    }

    /**
     * Since co-deputies, multiple deputies may be matched (eg siblings at same postcode).
     */
    public function getLastMatchedDeputyNumbers(): array
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
    public function isLastMachedDeputyNdrEnabled(): bool
    {
        foreach ($this->lastMatchedPreRegistrationUsers as $match) {
            if ($match->getNdr()) {
                return true;
            }
        }

        return false;
    }

    public function isMultiDeputyCase(string $caseNumber): bool
    {
        $crMatches = $this->preRegistrationRepository->findByCaseNumber($caseNumber);

        return count($crMatches) > 1;
    }

    /**
     * @return PreRegistration[]
     */
    private function applyPostcodeFilter(mixed $preRegistrationMatches, string $deputyPostcode): array
    {
        $deputyPostcode = DataNormaliser::normalisePostcode($deputyPostcode);

        $preRegistrationByPostcode = [];
        $preRegistrationWithPostcodeCount = 0;
        foreach ($preRegistrationMatches as $match) {
            $postcode = DataNormaliser::normalisePostcode($match->getDeputyPostCode());

            if (!empty($match->getDeputyPostCode())) {
                $preRegistrationByPostcode[$postcode][] = $match;
                ++$preRegistrationWithPostcodeCount;
            }
        }

        if ($preRegistrationWithPostcodeCount < count($preRegistrationMatches)) {
            $filteredResults = $preRegistrationMatches;
        } else {
            $filteredResults = array_key_exists($deputyPostcode, $preRegistrationByPostcode) ? $preRegistrationByPostcode[$deputyPostcode] : [];
        }

        return $filteredResults;
    }

    /**
     * @return PreRegistration[]
     */
    private function getCaseNumberMatches(array $detailsToMatchOn): array
    {
        /** @var PreRegistration[] $caseNumberMatches */
        $caseNumberMatches = $this->preRegistrationRepository->findByCaseNumber($detailsToMatchOn['caseNumber']);

        if (0 === count($caseNumberMatches)) {
            $errorJson = json_encode([
                'search_terms' => $detailsToMatchOn,
            ]);

            throw new RuntimeException($errorJson, 460);
        }

        return $caseNumberMatches;
    }

    /**
     * @param PreRegistration[] $caseNumberMatches
     *
     * @return PreRegistration[]
     */
    private function filterByClientLastname(array $caseNumberMatches, array $detailsToMatchOn)
    {
        /** @var PreRegistration[] $caseNumberClientLastnameMatches */
        $caseNumberClientLastnameMatches = [];

        foreach ($caseNumberMatches as $match) {
            if (mb_strtolower($match->getClientLastname()) == mb_strtolower($detailsToMatchOn['clientLastname'])) {
                $caseNumberClientLastnameMatches[] = $match;
            }
        }

        if (0 === count($caseNumberClientLastnameMatches)) {
            $caseNumberMatches = $this->formatPreRegistraionMatchesForErrorOutput($caseNumberMatches);

            $errorJson = json_encode([
                'search_terms' => $detailsToMatchOn,
                'case_number_matches' => $caseNumberMatches,
            ]);

            throw new RuntimeException($errorJson, 461);
        }

        return $caseNumberClientLastnameMatches;
    }

    /**
     * @param PreRegistration[] $filteredCaseNumberMatches
     *
     * @return PreRegistration[]
     */
    private function filterByDeputyLastname(array $filteredCaseNumberMatches, array $detailsToMatchOn)
    {
        /** @var PreRegistration[] $filteredClientLastnameMatches */
        $filteredClientLastnameMatches = [];

        foreach ($filteredCaseNumberMatches as $match) {
            if (mb_strtolower($match->getDeputySurname()) == mb_strtolower($detailsToMatchOn['deputySurname'])) {
                $filteredClientLastnameMatches[] = $match;
            }
        }

        if (0 === count($filteredClientLastnameMatches)) {
            $filteredCaseNumberMatches = $this->formatPreRegistraionMatchesForErrorOutput($filteredCaseNumberMatches);

            $errorJson = json_encode([
                'search_terms' => $detailsToMatchOn,
                'client_last_name_matches' => $filteredCaseNumberMatches,
            ]);

            throw new RuntimeException($errorJson, 462);
        }

        return $filteredClientLastnameMatches;
    }

    /**
     * @param PreRegistration[] $matches
     *
     * @return PreRegistration[]
     */
    private function formatPreRegistraionMatchesForErrorOutput(array $matches): mixed
    {
        $matches = json_decode(
            $this->serializer->serialize($matches, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['otherColumns']]),
            true
        );

        return $matches;
    }
}

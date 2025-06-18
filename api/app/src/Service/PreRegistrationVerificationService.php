<?php

namespace App\Service;

use App\Entity\PreRegistration;
use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class PreRegistrationVerificationService
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly PreRegistrationRepository $preRegistrationRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Throw error 4** if preregistration has no record matching case number,
     * client surname, deputy firstname and surname, and postcode (if set).
     *
     * @return PreRegistration[]
     */
    public function validate(?string $caseNumber, ?string $clientLastname, ?string $deputyFirstname, ?string $deputyLastname, ?string $deputyPostcode): array
    {
        $detailsToMatchOn = [
            'caseNumber' => $caseNumber,
            'clientLastname' => $clientLastname,
            'deputyFirstname' => $deputyFirstname,
            'deputyLastname' => $deputyLastname,
            'deputyPostcode' => $deputyPostcode,
        ];

        $caseNumberMatches = $this->getCaseNumberMatches($detailsToMatchOn);

        $preregMatches = $this->checkOtherDetailsMatch($caseNumberMatches, $detailsToMatchOn);

        return $preregMatches;
    }

    public function isMultiDeputyCase(string $caseNumber): bool
    {
        $crMatches = $this->preRegistrationRepository->findByCaseNumber($caseNumber);

        return count($crMatches) > 1;
    }

    public function deputyUidHasOtherUserAccounts(string $deputyUid): bool
    {
        $existingDeputyAccounts = $this->userRepository->findBy(['deputyUid' => intval($deputyUid)]);

        return count($existingDeputyAccounts) > 0;
    }

    /**
     * @return PreRegistration[]
     */
    private function getCaseNumberMatches(array $detailsToMatchOn): array
    {
        /** @var PreRegistration[] $caseNumberMatches */
        $caseNumberMatches = $this->preRegistrationRepository->findByCaseNumber($detailsToMatchOn['caseNumber'] ?? '');

        if (0 === count($caseNumberMatches)) {
            $errorJson = json_encode([
                'search_terms' => $detailsToMatchOn,
            ]);

            throw new \RuntimeException($errorJson, 460);
        }

        return $caseNumberMatches;
    }

    /**
     * @param PreRegistration[]      $caseNumberMatches
     * @param array<string, ?string> $detailsToMatchOn  Map of field => value derived from user-submitted reg data
     *
     * @return PreRegistration[]
     *
     * This will throw a runtime exception if no matches were found, with details of where the match failed
     * (with structure as per $matchingErrors array); an entry in this array means that none of the potential
     * matches matched against that field
     */
    private function checkOtherDetailsMatch(array $caseNumberMatches, array $detailsToMatchOn): array
    {
        $matchingErrors = ['client_lastname' => false, 'deputy_firstname' => false, 'deputy_lastname' => false, 'deputy_postcode' => false];

        /** @var PreRegistration[] $clientLastnameMatches */
        $clientLastnameMatches = [];

        $userSubmittedClientLastName = $this->normaliseName($detailsToMatchOn['clientLastname']);
        foreach ($caseNumberMatches as $match) {
            if (
                !is_null($userSubmittedClientLastName)
                && $this->normaliseName($match->getClientLastname()) === $userSubmittedClientLastName
            ) {
                $clientLastnameMatches[] = $match;
            }
        }

        if (0 === count($clientLastnameMatches)) {
            $matchingErrors['client_lastname'] = true;
            $clientLastnameMatches = $caseNumberMatches;
        }

        /** @var PreRegistration[] $deputyLastnameMatches */
        $deputyLastnameMatches = [];

        $userSubmittedDeputyLastName = $this->normaliseName($detailsToMatchOn['deputyLastname']);
        foreach ($clientLastnameMatches as $match) {
            if (
                !is_null($userSubmittedDeputyLastName)
                && $this->normaliseName($match->getDeputySurname()) === $userSubmittedDeputyLastName
            ) {
                $deputyLastnameMatches[] = $match;
            }
        }

        if (0 === count($deputyLastnameMatches)) {
            $matchingErrors['deputy_lastname'] = true;
            $deputyLastnameMatches = $clientLastnameMatches;
        }

        /** @var PreRegistration[] $deputyFirstnameMatches */
        $deputyFirstnameMatches = [];

        $userSubmittedDeputyFirstName = $this->normaliseName($detailsToMatchOn['deputyFirstname']);
        foreach ($deputyLastnameMatches as $match) {
            if (
                !is_null($userSubmittedDeputyFirstName)
                && $this->normaliseName($match->getDeputyFirstname()) === $userSubmittedDeputyFirstName
            ) {
                $deputyFirstnameMatches[] = $match;
            }
        }

        if (0 === count($deputyFirstnameMatches)) {
            $matchingErrors['deputy_firstname'] = true;
            $deputyFirstnameMatches = $deputyLastnameMatches;
        }

        if (isset($detailsToMatchOn['deputyPostcode'])) {
            $normalisedPostcode = DataNormaliser::normalisePostcode($detailsToMatchOn['deputyPostcode']);
            $preRegistrationByPostcode = [];
            $preRegistrationWithPostcodeCount = 0;

            foreach ($deputyFirstnameMatches as $match) {
                $postcode = DataNormaliser::normalisePostcode($match->getDeputyPostCode());

                if (!empty($match->getDeputyPostCode())) {
                    $preRegistrationByPostcode[$postcode][] = $match;
                    ++$preRegistrationWithPostcodeCount;
                }
            }

            if ($preRegistrationWithPostcodeCount < count($deputyFirstnameMatches)) {
                $deputyPostcodeMatches = $deputyFirstnameMatches;
            } else {
                $deputyPostcodeMatches = array_key_exists($normalisedPostcode, $preRegistrationByPostcode) ? $preRegistrationByPostcode[$normalisedPostcode] : [];
            }

            if (0 === count($deputyPostcodeMatches)) {
                $matchingErrors['deputy_postcode'] = true;
                $deputyPostcodeMatches = $deputyFirstnameMatches;
            }

            $finalMatchingCases = $deputyPostcodeMatches;
        } else {
            $finalMatchingCases = $deputyFirstnameMatches;
        }

        if (in_array(true, $matchingErrors)) {
            $formattedPreRegistrationMatches = $this->formatPreRegistrationMatchesForErrorOutput($caseNumberMatches);

            $errorJson = json_encode([
                'search_terms' => $detailsToMatchOn,
                'case_number_matches' => $formattedPreRegistrationMatches,
                'matching_errors' => $matchingErrors,
            ]);

            throw new \RuntimeException($errorJson, 461);
        }

        return $finalMatchingCases;
    }

    /**
     * @param PreRegistration[] $matches
     *
     * @return PreRegistration[]
     */
    private function formatPreRegistrationMatchesForErrorOutput(array $matches): mixed
    {
        $matches = json_decode(
            $this->serializer->serialize($matches, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['otherColumns']]) ?? '',
            true
        );

        return $matches;
    }

    private function normaliseName(?string $name): ?string
    {
        if (is_null($name)) {
            return null;
        }

        $normalisedName = str_replace('’', '\'', $name);

        return trim(mb_strtolower($normalisedName));
    }
}

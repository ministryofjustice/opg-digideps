<?php

namespace App\Service;

use App\Entity\PreRegistration;
use App\Repository\PreRegistrationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class PreRegistrationVerificationService
{
    /**
     * @var PreRegistration[]
     */
    private array $lastMatchedPreRegistrationUsers;

    public function __construct(private SerializerInterface $serializer, private PreRegistrationRepository $preRegistrationRepository, private UserRepository $userRepository)
    {
        $this->lastMatchedPreRegistrationUsers = [];
    }

    /**
     * Throw error 400 if preregistration has no record matching case number,
     * client surname, deputy firstname and surname, and postcode (if set).
     */
    public function validate(string $caseNumber, string $clientLastname, string $deputyFirstname, string $deputyLastname, ?string $deputyPostcode): bool
    {
        $detailsToMatchOn = [
            'caseNumber' => $caseNumber,
            'clientLastname' => $clientLastname,
            'deputyFirstname' => $deputyFirstname,
            'deputyLastname' => $deputyLastname,
        ];

        if ($deputyPostcode) {
            $detailsToMatchOn['deputyPostcode'] = $deputyPostcode;
        }

        $caseNumberMatches = $this->getCaseNumberMatches($detailsToMatchOn);

        $this->lastMatchedPreRegistrationUsers = $this->checkOtherDetailsMatch($caseNumberMatches, $detailsToMatchOn);

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

    public function isFirstTimeDeputySigningUp(): bool
    {
        $deputyUid = $this->getLastMatchedDeputyNumbers()[0];
        $existingDeputyAccounts = $this->userRepository->findBy(['deputyUid' => intval($deputyUid)]);

        return !$existingDeputyAccounts;
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

            throw new \RuntimeException($errorJson, 460);
        }

        return $caseNumberMatches;
    }

    /**
     * @param PreRegistration[] $caseNumberMatches
     *
     * @return PreRegistration[]
     */
    private function checkOtherDetailsMatch(array $caseNumberMatches, $detailsToMatchOn)
    {
        $matchingErrors = ['client_lastname' => false, 'deputy_firstname' => false, 'deputy_lastname' => false, 'deputy_postcode' => false];

        /** @var PreRegistration[] $clientLastnameMatches */
        $clientLastnameMatches = [];

        foreach ($caseNumberMatches as $match) {
            if ($this->normaliseName($match->getClientLastname()) === $this->normaliseName($detailsToMatchOn['clientLastname'])) {
                $clientLastnameMatches[] = $match;
            }
        }

        if (0 === count($clientLastnameMatches)) {
            $matchingErrors['client_lastname'] = true;
            $clientLastnameMatches = $caseNumberMatches;
        }

        /** @var PreRegistration[] $deputyLastnameMatches */
        $deputyLastnameMatches = [];

        foreach ($clientLastnameMatches as $match) {
            if ($this->normaliseName($match->getDeputySurname()) === $this->normaliseName($detailsToMatchOn['deputyLastname'])) {
                $deputyLastnameMatches[] = $match;
            }
        }

        if (0 === count($deputyLastnameMatches)) {
            $matchingErrors['deputy_lastname'] = true;
            $deputyLastnameMatches = $clientLastnameMatches;
        }

        /** @var PreRegistration[] $deputyFirstnameMatches */
        $deputyFirstnameMatches = [];

        foreach ($deputyLastnameMatches as $match) {
            if ($this->normaliseName($match->getDeputyFirstname()) === $this->normaliseName($detailsToMatchOn['deputyFirstname'])) {
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
            $this->serializer->serialize($matches, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['otherColumns']]),
            true
        );

        return $matches;
    }

    private function normaliseName(string $name): string
    {
        $normalisedName = str_replace('’', '\'', $name);

        return trim(mb_strtolower($normalisedName));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Tests\Behat\BehatException;

trait UserExistsTrait
{
    /**
     * @Given a Lay Deputy exists
     *
     * @throws BehatException
     */
    public function aLayDeputyExists()
    {
        if (empty($this->layDeputyNotStartedHealthWelfareDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $layDeputyNotStartedHealthWelfareDetails');
        }

        $this->interactingWithUserDetails = $this->layDeputyNotStartedHealthWelfareDetails;
    }

    /**
     * @Given a Lay Deputy exists with a legacy password hash
     *
     * @throws BehatException
     */
    public function aLayDeputyExistsWithLegacyPasswordHash()
    {
        if (empty($this->layDeputyNotStartedPfaHighAssetsDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaHighAssetsDetails');
        }

        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaHighAssetsDetails;
    }

    /**
     * @Given /^a Professional Admin Deputy exists$/
     */
    public function aProfessionalAdminDeputyExists()
    {
        if (empty($this->profAdminCombinedHighNotStartedDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $profAdminCombinedHighNotStartedDetails');
        }

        $this->interactingWithUserDetails = $this->profAdminCombinedHighNotStartedDetails;
    }

    /**
     * @Given /^a Professional Team Deputy exists$/
     */
    public function aProfessionalTeamDeputyExists()
    {
        if (empty($this->profTeamDeputyNotStartedHealthWelfareDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $profTeamDeputyNotStartedHealthWelfareDetails');
        }

        $this->interactingWithUserDetails = $this->profTeamDeputyNotStartedHealthWelfareDetails;
    }

    /**
     * @Given a Multi-client Lay Deputy exists and I select the non-primary user
     *
     * @throws BehatException
     */
    public function aMultiClientLayDeputyExists()
    {
        if (empty($this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $layPfaHighNotStartedMultiClientDeputyNonPrimaryUser');
        }

        $this->interactingWithUserDetails = $this->layPfaHighNotStartedMultiClientDeputyNonPrimaryUser;
    }
}

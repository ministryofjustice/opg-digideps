<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Common;

use Tests\OPG\Digideps\Backend\Behat\BehatException;

trait UserExistsTrait
{
    /**
     * @Given a Lay Deputy exists
     *
     * @throws BehatException
     */
    public function aLayDeputyExists(): void
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
    public function aLayDeputyExistsWithLegacyPasswordHash(): void
    {
        if (empty($this->layDeputyNotStartedPfaHighAssetsDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaHighAssetsDetails');
        }

        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaHighAssetsDetails;
    }

    /**
     * @Given /^a Professional Admin Deputy exists$/
     */
    public function aProfessionalAdminDeputyExists(): void
    {
        if (empty($this->profAdminCombinedHighNotStartedDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $profAdminCombinedHighNotStartedDetails');
        }

        $this->interactingWithUserDetails = $this->profAdminCombinedHighNotStartedDetails;
    }

    /**
     * @Given /^a Professional Team Deputy exists$/
     */
    public function aProfessionalTeamDeputyExists(): void
    {
        if (empty($this->profTeamDeputyNotStartedHealthWelfareDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $profTeamDeputyNotStartedHealthWelfareDetails');
        }

        $this->interactingWithUserDetails = $this->profTeamDeputyNotStartedHealthWelfareDetails;
    }
}

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
}

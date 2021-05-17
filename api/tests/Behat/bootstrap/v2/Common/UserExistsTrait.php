<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait UserExistsTrait
{
    /**
     * @Given a Lay Deputy exists
     *
     * @throws Exception
     */
    public function aLayDeputyExists()
    {
        if (empty($this->layDeputyNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedDetails');
        }

        $this->interactingWithUserDetails = $this->layDeputyNotStartedDetails;
    }
}

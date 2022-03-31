<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait INavigateToFrontendTrait
{
    /**
     * @When /^I navigate to the upload further documents page$/
     */
    public function iNavigateToTheUploadFurtherDocumentsPage()
    {
        $this->clickLink('Attach documents');
        $this->iAmOnFurtherUploadDocumentsPage();
    }
}

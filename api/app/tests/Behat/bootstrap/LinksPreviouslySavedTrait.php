<?php

namespace App\Tests\Behat;

trait LinksPreviouslySavedTrait
{
    private static $linksCache = [];

    /**
     * @When I save the current URL as :urlId
     */
    public function iSaveTheCurrentUrlAs($urlId)
    {
        self::$linksCache[$urlId] = $this->getSession()->getCurrentUrl();
    }

    /**
     * @When I go to the URL previously saved as :urlId
     */
    public function iGoToTheUrlPreviouslySavedAs($urlId)
    {
        $previouslySavedUrl = self::getPreviouslySavedLinkByUrlId($urlId);
        $this->visitPath($previouslySavedUrl);
    }

    /**
     * @When the current URL should match with the URL previously saved as :urlId
     */
    public function theCurrentUrlShoulMatchWithTheUrlPreviouslySavedAs($urlId)
    {
        $previouslySavedUrl = self::getPreviouslySavedLinkByUrlId($urlId);
        $currentUrl = $this->getSession()->getCurrentUrl();
        if ($currentUrl !== $previouslySavedUrl) {
            throw new \Exception("$currentUrl not the same as expected $previouslySavedUrl");
        }
    }

    /**
     * @param string $urlId
     *
     * @return string
     *
     * @throws \Exception
     */
    private static function getPreviouslySavedLinkByUrlId($urlId)
    {
        if (empty(self::$linksCache[$urlId])) {
            throw new \Exception("$urlId not saved");
        }

        return self::$linksCache[$urlId];
    }
}

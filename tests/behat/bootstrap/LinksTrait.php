<?php

namespace DigidepsBehat;

trait LinksTrait
{

    /**
     * @Then the :text link url should contain ":expectedLink"
     */
    public function linkWithTextContains($text, $expectedLink)
    {
    
        $linksElementsFound = $this->getSession()->getPage()->find('xpath', '//a[text()="' . $text . '"]');
        $count = count($linksElementsFound);
        
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element not found");
        }
        
        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Returned multiple elements");
        }
    
        $href = $linksElementsFound->getAttribute('href');
        
        if (strpos($href, $expectedLink) === FALSE) {
            throw new \Exception("Link: $href does not contain $expectedLink");
        }

    }
    
    /**
     * @Given I visit the behat link :link
     */
    
    public function visitBehatLink($link)
    {
       $secret = md5('behat-dd-' . $this->getSymfonyParam('secret'));
       
       $this->visit("behat/{$secret}/{$link}");
    }


}
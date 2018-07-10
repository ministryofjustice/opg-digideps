<?php

namespace AppBundle\Entity;

/**
 * Common functionalities among Report and NDR
 */
interface BankAccountInterface
{
    public function getNameOneLine();

    public function getBank();

    public function getAccountTypeText();

    public function getOpeningBalance();

    public function getClosingBalance();

    public function getIsClosed();

    public function getIsJointAccount();

}

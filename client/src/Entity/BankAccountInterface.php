<?php

namespace App\Entity;

/**
 * Common functionalities among Report and NDR.
 */
interface BankAccountInterface
{
    public function getNameOneLine();

    public function getBank();

    public function getAccountTypeText();

    public function getClosingBalance();

    public function getIsClosed();

    public function getIsJointAccount();
}

<?php

namespace App\Entity;

interface BankAccountInterface
{
    public function getNameOneLine();

    public function getBank();

    public function getAccountTypeText();

    public function getClosingBalance();

    public function getIsClosed();

    public function getIsJointAccount();
}

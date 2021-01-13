<?php

namespace App\Entity;

interface BankAccountInterface
{
    public function getNameOneLine();

    public function getBank();

    public function getAccountTypeText();

    public function getOpeningBalance();

    public function getClosingBalance();

    public function getIsClosed();

    public function getIsJointAccount();

    /**
     * @return string
     */
    public function getAccountType();

    /**
     * @return string
     */
    public function getAccountNumber();

    /**
     * @return string
     */
    public function getSortCode();
}

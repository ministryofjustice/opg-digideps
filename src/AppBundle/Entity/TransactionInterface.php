<?php
namespace AppBundle\Entity;

interface TransactionInterface
{
    public function getTotal();
    public function setAccount(Account $account = null);
    public function getAccount();
}
<?php
namespace AppBundle\Entity;

interface TransactionInterface
{
    public function getTotal();
    public function setAccount(\AppBundle\Entity\Account $account = null);
    public function getAccount();
}
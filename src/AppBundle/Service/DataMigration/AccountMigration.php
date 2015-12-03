<?php

namespace AppBundle\Service\DataMigration;

use PDO;

class AccountMigration
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrateAccounts()
    {

    }

}
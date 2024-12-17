<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version286 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds mirgration script';
    }

    public function up(Schema $schema): void
    {
        if (getenv('RUN_ONE_OFF_MIGRATIONS')) {
            $sql = <<<SQL
            INSERT INTO deputy 
            (user_id, deputy_uid, firstname, lastname, email1, address1, address2, 
            address3, address4, address5, address_postcode, address_country, phone_main, phone_alternative)
            SELECT id, CAST(deputy_uid AS VARCHAR), firstname, lastname, email, address1, address2, address3, 
            address4, address5, address_postcode, address_country, phone_main, phone_alternative
            FROM dd_user
            WHERE id IN (
                SELECT u.id
                FROM client c
                LEFT JOIN deputy_case dc ON c.id = dc.client_id
                LEFT JOIN dd_user u ON dc.user_id = u.id
                WHERE dc.user_id IN (
                    SELECT id
                    FROM dd_user 
                    WHERE is_primary = TRUE
                    AND CAST(deputy_uid AS VARCHAR) NOT IN (
                        SELECT deputy_uid 
                        FROM deputy
                        )
                )
            )
            SQL;

            $this->addSql($sql);
        }
    }
}

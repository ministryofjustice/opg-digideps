<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1681';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_token (user_id INT NOT NULL, token UUID NOT NULL, PRIMARY KEY(user_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_69B8784A5F37A13B ON account_token (token)');
        $this->addSql('CREATE INDEX IDX_69B8784A5F37A13B ON account_token (token)');
        $this->addSql('COMMENT ON COLUMN account_token.token IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dd_user ALTER password DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE account_token');
        $this->addSql('ALTER TABLE dd_user ALTER password SET NOT NULL');
    }
}

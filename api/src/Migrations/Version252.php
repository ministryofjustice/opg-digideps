<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropping out UserResearchResponse entitys that were made before we associated Satisfactions with them and added a created_at field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71a5423f28f');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71a5423f28f FOREIGN KEY (researchtype_id) REFERENCES research_type(id) ON DELETE CASCADE');
        $this->addSql('DELETE FROM research_type WHERE id IN (SELECT rt.id FROM user_research_response as urr INNER JOIN research_type as rt ON urr.researchtype_id = rt.id WHERE urr.created_at IS NULL);');
    }

    public function down(Schema $schema): void
    {
        // impossible to come back from this as we're deleting records
    }
}

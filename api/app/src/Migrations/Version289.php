<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version288 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        /* these migrations are all valid fixes to existing database issues */

        // let Doctrine rename indices if it keeps it happy
        $this->addSql('ALTER INDEX idx_c744045595162e7c RENAME TO IDX_C74404554B6F93BB');
        $this->addSql('ALTER INDEX uniq_1058689d625b8d2 RENAME TO UNIQ_28FA6B9F64B76B02');
        $this->addSql('ALTER INDEX updated_at_index RENAME TO updated_at_idx');

        // there are no null dd_user.lastname or deputy.deputy_uid fields in the live data
        // so we can safely add these constraints
        $this->addSql('ALTER TABLE dd_user ALTER lastname SET NOT NULL');
        $this->addSql('ALTER TABLE deputy ALTER deputy_uid SET NOT NULL');

        // increase pre_registration.client_firstname length from 50 to 100 to match firstname field lengths in other
        // tables; will not truncate any data as we're increasing the field's length
        $this->addSql('ALTER TABLE pre_registration ALTER client_firstname TYPE VARCHAR(100)');

        // pre_registration.client_postcode is a varchar(255) field, which is not necessary for postcodes, so
        // reduce to 10 characters; no existing postcodes are longer than 10 characters so we won't lose data
        $this->addSql('ALTER TABLE pre_registration ALTER client_postcode TYPE VARCHAR(10)');

        // this is a text field which is prone to abuse, so truncate to a reasonable length varchar instead;
        // valid comments longer than 1200 characters will be exported before this migration is applied;
        // NB the 1200 character limit is already on the Satisfaction.comments entity property
        $this->addSql('ALTER TABLE satisfaction ALTER comments TYPE VARCHAR(1200)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE satisfaction ALTER comments TYPE TEXT');
        $this->addSql('ALTER TABLE deputy ALTER deputy_uid DROP NOT NULL');
        $this->addSql('ALTER INDEX uniq_28fa6b9f64b76b02 RENAME TO uniq_1058689d625b8d2');
        $this->addSql('ALTER TABLE dd_user ALTER lastname DROP NOT NULL');
        $this->addSql('ALTER TABLE pre_registration ALTER client_firstname TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE pre_registration ALTER client_postcode TYPE VARCHAR(255)');
        $this->addSql('ALTER INDEX updated_at_idx RENAME TO updated_at_index');
        $this->addSql('ALTER INDEX idx_c74404554b6f93bb RENAME TO idx_c744045595162e7c');
    }
}

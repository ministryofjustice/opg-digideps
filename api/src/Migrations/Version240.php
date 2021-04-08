<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version240 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create court_order_address rows for all existing lay based court_order_deputys';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("
insert into court_order_address (court_order_deputy_id, addressline1, addressline2, addressline3, postcode, country)
select
  cod.id,
  u.address1,
  u.address2,
  u.address3,
  u.address_postcode,
  u.address_country
from court_order_deputy cod
inner join dd_user u on u.id = cod.user_id
where u.role_name = 'ROLE_LAY_DEPUTY'
and u.active = true
       ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("
delete from court_order_address where id in
(
	select coa.id
	from court_order_address coa
	inner join court_order_deputy cod on cod.id = coa.court_order_deputy_id
	inner join dd_user u on u.id = cod.user_id
	where u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
)
;
        ");
    }
}

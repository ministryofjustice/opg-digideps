<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create court_order_deputy rows for all existing lay based court_orders';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("
insert into court_order_deputy (court_order_id, user_id, organisation_id, deputynumber, firstname, surname, email)
select
  co.id,
  u.id,
  null,
  u.deputy_no,
  u.firstname,
  u.lastname,
  u.email
from dd_user u
inner join deputy_case dc on dc.user_id = u.id
inner join court_order co on co.client_id = dc.client_id
-- theoretically no Lay deputy will have a row in organisation_user but we will left join and filter just in case
left join organisation_user ou on ou.user_id = u.id
-- we dont want to create a court_order_deputy for court_orders that already have one.
-- left join to ensure we only get those who DO NOT join (see where cod.id is null clause below).
left join court_order_deputy cod on cod.user_id = dc.user_id
-- three filters to ensure we are only creating court_order_deputy rows for court_orders belonging to lay deputies
-- this will also prevent creating duplicates for the non lay deputy court_orders that currently exist on prod
where u.role_name = 'ROLE_LAY_DEPUTY'
and u.active = true
and ou.organisation_id is null
and cod.id is null
;
       ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("
delete from court_order_deputy where id in
(
	select cod.id
	from court_order_deputy cod
	inner join dd_user u on u.id = cod.user_id
	where u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
)
;
        ");
    }
}

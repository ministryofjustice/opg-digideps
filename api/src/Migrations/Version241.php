<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version241 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Attach all relevant lay based reports and NDRs to each lay based court order';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("
update report r set court_order_id =
COALESCE((
	select MAX(co.id)
	from court_order co
	inner join court_order_deputy cod on cod.court_order_id = co.id
	inner join dd_user u on u.id = cod.user_id
	where r.court_order_id is null
	and co.client_id = r.client_id
	and u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
), court_order_id)
       ");

        $this->addSql("
update odr o set court_order_id =
COALESCE((
	select MAX(co.id)
	from court_order co
	inner join court_order_deputy cod on cod.court_order_id = co.id
	inner join dd_user u on u.id = cod.user_id
	where o.court_order_id is null
	and co.client_id = o.client_id
	and u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
), court_order_id)
       ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("
update report r set court_order_id = null
where r.id in
(
	select r2.id
	from report r2
	inner join court_order co on co.client_id = r2.client_id
	inner join court_order_deputy cod on cod.court_order_id = co.id
	inner join dd_user u on u.id = cod.user_id
	and co.client_id = r2.client_id
	and u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
)
        ");

        $this->addSql("
update odr o set court_order_id = null
where o.id in
(
	select o2.id
	from odr o2
	inner join court_order co on co.client_id = o2.client_id
	inner join court_order_deputy cod on cod.court_order_id = co.id
	inner join dd_user u on u.id = cod.user_id
	and co.client_id = o2.client_id
	and u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
)
        ");
    }
}

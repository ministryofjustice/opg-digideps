<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version238 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create court_order rows for all existing lay clients';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("
insert into court_order (client_id, type, supervision_level, order_date, case_number)
select
  distinct(c.id),
  case
    -- if client does not have a report (only has an NDR) then type at this point is NULL
    when r.id is null then null
    -- otherwise assign HW for any '4' based reports, and PFA for all others
    when r.type = '102_4'
      or r.type = '103_4'
      or r.type = '104'
      then 'HW'
    else 'PFA'
  end,
  case
 	when r.type = '102' or r.type = '102_4' then 'GENERAL'
 	when r.type = '103' or r.type = '103_4' then 'MINIMAL'
 	-- null for HW only (104)
 	else null
  end,
  case
    -- can be nullable as some clients may no longer be in casrec table but we still want a court_order created
    when ca.order_date is not null then ca.order_date
    else null
  end,
  c.case_number
from client c
-- left join as some clients may not have a report but an odr instead
-- a client likely has > 1 report but we only want 1 report in order to read the r.type - using MAX(due_date) as selection criteria
left join report r on r.client_id = c.id and r.due_date = (select MAX(due_date) from report where client_id = c.id)
-- left join as some clients will not have an odr (but a report instead)
left join odr o on o.client_id = c.id
-- we dont want to create a court_order for clients who already have one.
-- left join to ensure we only get those who DO NOT join (see where co.id is null clause below).
left join court_order co on co.case_number = c.case_number
-- left join as not all clients will still exist in casrec table but still want to create a court_order for those who dont
left join casrec ca on ca.client_case_number = c.case_number
-- joins to establish filter on user role and status
inner join deputy_case dc on dc.client_id = c.id
inner join dd_user u on u.id = dc.user_id
where u.role_name = 'ROLE_LAY_DEPUTY'
and u.active = true
-- ensure client has at least a report OR an odr
and (r.id is not null or o.id is not null)
-- ensure a join was NOT made on an existing court_order
and co.id is null
;
       ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("
delete from court_order where id in
(
	select co.id
	from court_order co
	inner join court_order_deputy cod on cod.court_order_id = co.id
	inner join dd_user u on u.id = cod.user_id
	where u.role_name = 'ROLE_LAY_DEPUTY'
	and u.active = true
)
        ");
    }
}
